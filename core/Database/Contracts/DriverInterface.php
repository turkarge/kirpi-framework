<?php

declare(strict_types=1);

namespace Core\Database\Contracts;

interface DriverInterface
{
    public function connect(array $config): void;
    public function disconnect(): void;
    public function isConnected(): bool;
    public function ping(): bool;

    public function select(string $query, array $bindings = []): array;
    public function insert(string $query, array $bindings = []): int;
    public function update(string $query, array $bindings = []): int;
    public function delete(string $query, array $bindings = []): int;
    public function statement(string $query, array $bindings = []): bool;

    public function beginTransaction(): void;
    public function commit(): void;
    public function rollback(): void;
    public function inTransaction(): bool;

    public function getDriverName(): string;
    public function getLastInsertId(): int|string;
}