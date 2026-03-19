<?php

declare(strict_types=1);

namespace Core\Database\Drivers;

use Core\Database\Contracts\DriverInterface;
use Core\Database\Exceptions\DatabaseException;

class SQLiteDriver implements DriverInterface
{
    private ?\PDO $pdo = null;

    public function connect(array $config): void
    {
        $database = $config['database'] ?? ':memory:';

        try {
            $this->pdo = new \PDO("sqlite:{$database}");
            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_OBJ);

            $this->pdo->exec('PRAGMA journal_mode = WAL');
            $this->pdo->exec('PRAGMA synchronous = NORMAL');
            $this->pdo->exec('PRAGMA cache_size = -64000');
            $this->pdo->exec('PRAGMA temp_store = MEMORY');
            $this->pdo->exec('PRAGMA foreign_keys = ON');

        } catch (\PDOException $e) {
            throw new DatabaseException(
                "SQLite connection failed: {$e->getMessage()}",
                0,
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
        return $this->execute($query, $bindings)->fetchAll();
    }

    public function insert(string $query, array $bindings = []): int
    {
        $this->execute($query, $bindings);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(string $query, array $bindings = []): int
    {
        return $this->execute($query, $bindings)->rowCount();
    }

    public function delete(string $query, array $bindings = []): int
    {
        return $this->execute($query, $bindings)->rowCount();
    }

    public function statement(string $query, array $bindings = []): bool
    {
        $this->execute($query, $bindings);
        return true;
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
        return 'sqlite';
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
            $stmt->execute($bindings);
            return $stmt;
        } catch (\PDOException $e) {
            throw new DatabaseException(
                "SQLite query failed: {$e->getMessage()} | SQL: {$query}",
                0,
                $e
            );
        }
    }
}