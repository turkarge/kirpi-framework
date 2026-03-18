<?php

declare(strict_types=1);

namespace Core\Config;

class Repository
{
    private array $items  = [];
    private array $loaded = [];

    public function __construct(private readonly string $configPath) {}

    // ─── Okuma ───────────────────────────────────────────────

    public function get(string $key, mixed $default = null): mixed
    {
        // "database.connections.mysql" → ['database', 'connections', 'mysql']
        $segments = explode('.', $key);
        $file     = array_shift($segments);

        $this->loadFile($file);

        if (empty($segments)) {
            return $this->items[$file] ?? $default;
        }

        return $this->getNestedValue(
            $this->items[$file] ?? [],
            $segments,
            $default
        );
    }

    public function set(string $key, mixed $value): void
    {
        $segments = explode('.', $key);
        $file     = array_shift($segments);

        $this->loadFile($file);

        if (empty($segments)) {
            $this->items[$file] = $value;
            return;
        }

        $this->setNestedValue($this->items[$file], $segments, $value);
    }

    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    public function all(): array
    {
        return $this->items;
    }

    // Tüm config dosyasını yükle
    public function load(string $file): array
    {
        $this->loadFile($file);
        return $this->items[$file] ?? [];
    }

    // ─── Cache ───────────────────────────────────────────────

    public function cache(string $cachePath): void
    {
        $this->loadAll();

        $content = '<?php return ' . var_export($this->items, true) . ';';
        file_put_contents($cachePath, $content);
    }

    public function loadFromCache(string $cachePath): bool
    {
        if (!file_exists($cachePath)) {
            return false;
        }

        $this->items = require $cachePath;
        return true;
    }

    public function clearCache(string $cachePath): void
    {
        if (file_exists($cachePath)) {
            unlink($cachePath);
        }
    }

    // ─── Private ─────────────────────────────────────────────

    private function loadFile(string $file): void
    {
        if (isset($this->loaded[$file])) {
            return;
        }

        $path = $this->configPath . DIRECTORY_SEPARATOR . $file . '.php';

        if (!file_exists($path)) {
            $this->items[$file]  = [];
            $this->loaded[$file] = true;
            return;
        }

        $this->items[$file]  = require $path;
        $this->loaded[$file] = true;
    }

    private function loadAll(): void
    {
        $files = glob($this->configPath . DIRECTORY_SEPARATOR . '*.php');

        foreach ($files as $file) {
            $name = pathinfo($file, PATHINFO_FILENAME);
            $this->loadFile($name);
        }
    }

    private function getNestedValue(array $array, array $keys, mixed $default): mixed
    {
        foreach ($keys as $key) {
            if (!is_array($array) || !array_key_exists($key, $array)) {
                return $default;
            }
            $array = $array[$key];
        }

        return $array;
    }

    private function setNestedValue(mixed &$array, array $keys, mixed $value): void
    {
        $key = array_shift($keys);

        if (empty($keys)) {
            $array[$key] = $value;
            return;
        }

        if (!isset($array[$key]) || !is_array($array[$key])) {
            $array[$key] = [];
        }

        $this->setNestedValue($array[$key], $keys, $value);
    }
}