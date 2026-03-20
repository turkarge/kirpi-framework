<?php

declare(strict_types=1);

namespace Core\Cache\Drivers;

use Core\Cache\CacheDriverInterface;

class RedisDriver implements CacheDriverInterface
{
    private \Redis $redis;
    private string $prefix;

    public function __construct(array $config)
    {
        $this->prefix = $config['prefix'] ?? 'kirpi_cache:';
        $this->redis  = new \Redis();

        $this->redis->connect(
            $config['host']     ?? '127.0.0.1',
            (int) ($config['port'] ?? 6379),
            2.5 // timeout
        );

        if (!empty($config['password'])) {
            $this->redis->auth($config['password']);
        }

        if (isset($config['database'])) {
            $this->redis->select((int) $config['database']);
        }
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $value = $this->redis->get($this->prefix . $key);

        if ($value === false) return $default;

        return unserialize($value);
    }

    public function set(string $key, mixed $value, int $ttl = 3600): bool
    {
        $serialized = serialize($value);

        if ($ttl === 0) {
            return $this->redis->set($this->prefix . $key, $serialized);
        }

        return $this->redis->setex($this->prefix . $key, $ttl, $serialized);
    }

    public function delete(string $key): bool
    {
        return $this->redis->del($this->prefix . $key) > 0;
    }

    public function exists(string $key): bool
    {
        return (bool) $this->redis->exists($this->prefix . $key);
    }

    public function increment(string $key, int $value = 1): int
    {
        return (int) $this->redis->incrBy($this->prefix . $key, $value);
    }

    public function decrement(string $key, int $value = 1): int
    {
        return (int) $this->redis->decrBy($this->prefix . $key, $value);
    }

    public function flush(): bool
    {
        $keys = $this->redis->keys($this->prefix . '*');

        if (!empty($keys)) {
            $this->redis->del($keys);
        }

        return true;
    }

    public function many(array $keys): array
    {
        $prefixed = array_map(fn($k) => $this->prefix . $k, $keys);
        $values   = $this->redis->mGet($prefixed);
        $result   = [];

        foreach ($keys as $i => $key) {
            $result[$key] = $values[$i] !== false
                ? unserialize($values[$i])
                : null;
        }

        return $result;
    }

    public function setMany(array $values, int $ttl = 3600): bool
    {
        foreach ($values as $key => $value) {
            $this->set($key, $value, $ttl);
        }
        return true;
    }

    public function deleteMany(array $keys): bool
    {
        $prefixed = array_map(fn($k) => $this->prefix . $k, $keys);
        $this->redis->del($prefixed);
        return true;
    }

    public function remember(string $key, int $ttl, \Closure $callback): mixed
    {
        $value = $this->get($key);

        if ($value !== null) {
            return $value;
        }

        $value = $callback();
        $this->set($key, $value, $ttl);

        return $value;
    }

    public function ttl(string $key): int
    {
        return (int) $this->redis->ttl($this->prefix . $key);
    }

    public function tags(array $tags): TaggedCache
    {
        return new TaggedCache($this, $tags);
    }
}