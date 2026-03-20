<?php

declare(strict_types=1);

namespace Core\Cache;

interface CacheDriverInterface
{
    public function get(string $key, mixed $default = null): mixed;
    public function set(string $key, mixed $value, int $ttl = 3600): bool;
    public function delete(string $key): bool;
    public function exists(string $key): bool;
    public function increment(string $key, int $value = 1): int;
    public function decrement(string $key, int $value = 1): int;
    public function flush(): bool;
    public function many(array $keys): array;
    public function setMany(array $values, int $ttl = 3600): bool;
    public function deleteMany(array $keys): bool;
    public function remember(string $key, int $ttl, \Closure $callback): mixed;
}