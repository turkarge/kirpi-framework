<?php

declare(strict_types=1);

namespace Core\Cache\Drivers;

use Core\Cache\CacheDriverInterface;

class FileDriver implements CacheDriverInterface
{
    private string $path;

    public function __construct(array $config)
    {
        $this->path = rtrim($config['path'] ?? storage_path('framework/cache'), '/');

        if (!is_dir($this->path)) {
            mkdir($this->path, 0755, true);
        }
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $file = $this->filePath($key);

        if (!file_exists($file)) return $default;

        $data = unserialize(file_get_contents($file));

        if ($data['expires_at'] !== 0 && $data['expires_at'] < time()) {
            $this->delete($key);
            return $default;
        }

        return $data['value'];
    }

    public function set(string $key, mixed $value, int $ttl = 3600): bool
    {
        $data = [
            'value'      => $value,
            'expires_at' => $ttl === 0 ? 0 : time() + $ttl,
            'created_at' => time(),
        ];

        return file_put_contents(
            $this->filePath($key),
            serialize($data),
            LOCK_EX
        ) !== false;
    }

    public function delete(string $key): bool
    {
        $file = $this->filePath($key);

        if (file_exists($file)) {
            return unlink($file);
        }

        return true;
    }

    public function exists(string $key): bool
    {
        return $this->get($key) !== null;
    }

    public function increment(string $key, int $value = 1): int
    {
        $current = (int) $this->get($key, 0);
        $new     = $current + $value;
        $this->set($key, $new, 0);
        return $new;
    }

    public function decrement(string $key, int $value = 1): int
    {
        return $this->increment($key, -$value);
    }

    public function flush(): bool
    {
        $files = glob($this->path . '/*.cache');

        foreach ($files ?: [] as $file) {
            unlink($file);
        }

        return true;
    }

    public function many(array $keys): array
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->get($key);
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
        foreach ($keys as $key) {
            $this->delete($key);
        }
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

    private function filePath(string $key): string
    {
        return $this->path . '/' . md5($key) . '.cache';
    }
}