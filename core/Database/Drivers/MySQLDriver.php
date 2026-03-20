<?php

declare(strict_types=1);

namespace Core\Database\Drivers;

use Core\Database\Contracts\DriverInterface;
use Core\Database\Exceptions\DatabaseException;

class MySQLDriver implements DriverInterface
{
    private ?\PDO $pdo = null;

public function connect(array $config): void
{
    $host     = $config['host']     ?? '127.0.0.1';
    $port     = $config['port']     ?? 3306;
    $database = $config['database'] ?? '';
    $charset  = $config['charset']  ?? 'utf8mb4';

    $dsn = "mysql:host={$host};port={$port};charset={$charset}";

    if (!empty($database)) {
        $dsn .= ";dbname={$database}";
    }

    $options = [
        \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ,
        \PDO::ATTR_EMULATE_PREPARES   => false,
        \PDO::ATTR_PERSISTENT         => false,
    ];

    try {
        $this->pdo = new \PDO(
            $dsn,
            $config['username'] ?? '',
            $config['password'] ?? '',
            $options
        );

        if (!empty($database)) {
            $this->pdo->exec("USE `{$database}`");
        }

    } catch (\PDOException $e) {
        throw new DatabaseException(
            "MySQL connection failed: {$e->getMessage()}",
            (int) $e->getCode(),
            $e
        );
    }
}

    public function disconnect(): void
    {
        $this->pdo = null;
    }

    public function isConnected(): bool
    {
        return $this->pdo !== null;
    }

    public function ping(): bool
    {
        try {
            $this->pdo?->query('SELECT 1');
            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    public function select(string $query, array $bindings = []): array
    {
        $stmt = $this->execute($query, $bindings);
        return $stmt->fetchAll();
    }

    public function insert(string $query, array $bindings = []): int
    {
        $this->execute($query, $bindings);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(string $query, array $bindings = []): int
    {
        $stmt = $this->execute($query, $bindings);
        return $stmt->rowCount();
    }

    public function delete(string $query, array $bindings = []): int
    {
        $stmt = $this->execute($query, $bindings);
        return $stmt->rowCount();
    }

    public function statement(string $query, array $bindings = []): bool
{
    if (!$this->isConnected()) {
        throw new DatabaseException('Not connected to database.');
    }

    try {
        if (empty($bindings)) {
            $this->pdo->exec($query);
        } else {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($bindings);
        }
        return true;
    } catch (\PDOException $e) {
        throw new DatabaseException(
            "Statement failed: {$e->getMessage()} | SQL: " . substr($query, 0, 100),
            (int) $e->getCode(),
            $e
        );
    }
}

    public function beginTransaction(): void
    {
        $this->pdo?->beginTransaction();
    }

    public function commit(): void
    {
        $this->pdo?->commit();
    }

    public function rollback(): void
    {
        $this->pdo?->rollBack();
    }

    public function inTransaction(): bool
    {
        return $this->pdo?->inTransaction() ?? false;
    }

    public function getDriverName(): string
    {
        return 'mysql';
    }

    public function getLastInsertId(): int|string
    {
        return (int) ($this->pdo?->lastInsertId() ?? 0);
    }

    private function execute(string $query, array $bindings = []): \PDOStatement
    {
        if (!$this->isConnected()) {
            throw new DatabaseException('Not connected to database.');
        }

        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($this->prepareBindings($bindings));
            return $stmt;
        } catch (\PDOException $e) {
            throw new DatabaseException(
                "Query failed: {$e->getMessage()} | SQL: {$query}",
                (int) $e->getCode(),
                $e
            );
        }
    }

    private function prepareBindings(array $bindings): array
    {
        return array_map(function ($value) {
            if ($value instanceof \DateTime) {
                return $value->format('Y-m-d H:i:s');
            }
            if (is_bool($value)) {
                return (int) $value;
            }
            return $value;
        }, $bindings);
    }
}