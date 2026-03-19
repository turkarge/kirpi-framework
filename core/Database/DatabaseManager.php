<?php

declare(strict_types=1);

namespace Core\Database;

use Core\Database\Contracts\DriverInterface;
use Core\Database\Drivers\MySQLDriver;
use Core\Database\Drivers\SQLiteDriver;
use Core\Database\Exceptions\DatabaseException;

class DatabaseManager
{
    private array $connections = [];
    private array $drivers     = [];
    private array $config;
    private string $default;

    public function __construct(array $config)
    {
        $this->config  = $config['connections'] ?? [];
        $this->default = $config['default']     ?? 'mysql';

        $this->registerDriver('mysql',  MySQLDriver::class);
        $this->registerDriver('sqlite', SQLiteDriver::class);
    }

    public function registerDriver(string $name, string $class): void
    {
        $this->drivers[$name] = $class;
    }

    public function connection(?string $name = null): DriverInterface
    {
        $name ??= $this->default;

        if (isset($this->connections[$name])) {
            return $this->connections[$name];
        }

        $config = $this->config[$name]
            ?? throw new DatabaseException("Connection [{$name}] not configured.");

        $driverName = $config['driver']
            ?? throw new DatabaseException("Driver not specified for [{$name}].");

        $driverClass = $this->drivers[$driverName]
            ?? throw new DatabaseException("Driver [{$driverName}] not registered.");

        $driver = new $driverClass();
        $driver->connect($config);

        return $this->connections[$name] = $driver;
    }

    public function table(string $table): QueryBuilder
    {
        return new QueryBuilder($this->connection(), $table);
    }

    public function on(string $connection): static
    {
        $clone              = clone $this;
        $clone->default     = $connection;
        $clone->connections = [];
        return $clone;
    }

    public function raw(string $sql, array $bindings = []): array
    {
        return $this->connection()->select($sql, $bindings);
    }

    public function beginTransaction(): void
    {
        $this->connection()->beginTransaction();
    }

    public function commit(): void
    {
        $this->connection()->commit();
    }

    public function rollback(): void
    {
        $this->connection()->rollback();
    }

    public function transaction(\Closure $callback): mixed
    {
        $this->beginTransaction();

        try {
            $result = $callback($this);
            $this->commit();
            return $result;
        } catch (\Throwable $e) {
            $this->rollback();
            throw $e;
        }
    }

    public function disconnect(?string $name = null): void
    {
        $name ??= $this->default;
        $this->connections[$name]?->disconnect();
        unset($this->connections[$name]);
    }

    public function reconnect(?string $name = null): DriverInterface
    {
        $this->disconnect($name);
        return $this->connection($name);
    }

    public function getConnections(): array
    {
        return array_keys($this->connections);
    }
}