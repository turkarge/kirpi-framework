<?php

declare(strict_types=1);

namespace Core\Cache;

use Core\Cache\Drivers\ArrayDriver;
use Core\Cache\Drivers\FileDriver;
use Core\Cache\Drivers\RedisDriver;

class CacheManager implements CacheDriverInterface
{
    private array $resolved = [];
    private array $config;
    private string $default;

    public function __construct(array $config)
    {
        $this->config  = $config['drivers'] ?? [];
        $this->default = $config['default'] ?? 'file';
    }

    // ─── Driver Seçimi ───────────────────────────────────────

    public function driver(?string $name = null): CacheDriverInterface
    {
        $name ??= $this->default;

        if (isset($this->resolved[$name])) {
            return $this->resolved[$name];
        }

        $config = $this->config[$name]
            ?? throw new \InvalidArgumentException("Cache driver [{$name}] not configured.");

        return $this->resolved[$name] = $this->createDriver($config);
    }

    private function createDriver(array $config): CacheDriverInterface
    {
        return match($config['driver']) {
            'redis' => new RedisDriver($config),
            'file'  => new FileDriver($config),
            'array' => new ArrayDriver(),
            default => throw new \RuntimeException("Cache driver [{$config['driver']}] not supported."),
        };
    }

    // ─── Proxy Methods ───────────────────────────────────────

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->driver()->get($key, $default);
    }

    public function set(string $key, mixed $value, int $ttl = 3600): bool
    {
        return $this->driver()->set($key, $value, $ttl);
    }

    public function delete(string $key): bool
    {
        return $this->driver()->delete($key);
    }

    public function exists(string $key): bool
    {
        return $this->driver()->exists($key);
    }

    public function increment(string $key, int $value = 1): int
    {
        return $this->driver()->increment($key, $value);
    }

    public function decrement(string $key, int $value = 1): int
    {
        return $this->driver()->decrement($key, $value);
    }

    public function flush(): bool
    {
        return $this->driver()->flush();
    }

    public function many(array $keys): array
    {
        return $this->driver()->many($keys);
    }

    public function setMany(array $values, int $ttl = 3600): bool
    {
        return $this->driver()->setMany($values, $ttl);
    }

    public function deleteMany(array $keys): bool
    {
        return $this->driver()->deleteMany($keys);
    }

    public function remember(string $key, int $ttl, \Closure $callback): mixed
    {
        return $this->driver()->remember($key, $ttl, $callback);
    }

    // ─── Forever ─────────────────────────────────────────────

    public function forever(string $key, mixed $value): bool
    {
        return $this->driver()->set($key, $value, 0);
    }

    // ─── Pull ────────────────────────────────────────────────

    public function pull(string $key, mixed $default = null): mixed
    {
        $value = $this->get($key, $default);
        $this->delete($key);
        return $value;
    }
}