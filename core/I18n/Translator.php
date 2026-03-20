<?php

declare(strict_types=1);

namespace Core\I18n;

class Translator
{
    private array  $loaded  = [];
    private string $locale;
    private string $fallback;
    private string $path;

    public function __construct(
        string $locale   = 'tr',
        string $fallback = 'en',
        string $path     = '',
    ) {
        $this->locale   = $locale;
        $this->fallback = $fallback;
        $this->path     = $path ?: BASE_PATH . '/lang';
    }

    // ─── Ana Çeviri Metodu ───────────────────────────────────

    public function get(string $key, array $replace = [], ?string $locale = null): string
    {
        $locale ??= $this->locale;

        // "auth.failed" → ['auth', 'failed']
        [$file, $lineKey] = $this->parseKey($key);

        $line = $this->getLine($file, $lineKey, $locale);

        // Fallback
        if ($line === null && $locale !== $this->fallback) {
            $line = $this->getLine($file, $lineKey, $this->fallback);
        }

        // Hâlâ null ise key'i döndür
        if ($line === null) return $key;

        return $this->makeReplacements($line, $replace);
    }

    // Kısa alias
    public function trans(string $key, array $replace = [], ?string $locale = null): string
    {
        return $this->get($key, $replace, $locale);
    }

    // Çoğul form
    public function choice(string $key, int $count, array $replace = [], ?string $locale = null): string
    {
        $line = $this->get($key, $replace, $locale);

        return $this->getPlural($line, $count, $replace);
    }

    // ─── Locale ──────────────────────────────────────────────

    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function getFallback(): string
    {
        return $this->fallback;
    }

    public function has(string $key, ?string $locale = null): bool
    {
        $locale ??= $this->locale;
        [$file, $lineKey] = $this->parseKey($key);
        return $this->getLine($file, $lineKey, $locale) !== null;
    }

    // ─── Private ─────────────────────────────────────────────

    private function parseKey(string $key): array
    {
        if (str_contains($key, '.')) {
            $parts = explode('.', $key, 2);
            return [$parts[0], $parts[1]];
        }

        return [$key, null];
    }

    private function getLine(string $file, ?string $key, string $locale): ?string
    {
        $this->load($file, $locale);

        $lines = $this->loaded[$locale][$file] ?? [];

        if ($key === null) {
            return null;
        }

        // Nested key desteği: "validation.min.string"
        $segments = explode('.', $key);
        $value    = $lines;

        foreach ($segments as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return null;
            }
            $value = $value[$segment];
        }

        return is_string($value) ? $value : null;
    }

    private function load(string $file, string $locale): void
    {
        if (isset($this->loaded[$locale][$file])) {
            return;
        }

        $path = $this->path . '/' . $locale . '/' . $file . '.php';

        if (!file_exists($path)) {
            $this->loaded[$locale][$file] = [];
            return;
        }

        $this->loaded[$locale][$file] = require $path;
    }

    private function makeReplacements(string $line, array $replace): string
    {
        foreach ($replace as $key => $value) {
            $line = str_replace(
                [':' . $key, ':' . strtoupper($key), ':' . ucfirst($key)],
                [$value, strtoupper($value), ucfirst($value)],
                $line
            );
        }

        return $line;
    }

    private function getPlural(string $line, int $count, array $replace): string
    {
        // Format: "tek form|çoğul form" veya "{0} hiç|{1} bir|[2,*] çok"
        $segments = explode('|', $line);

        if (count($segments) === 2) {
            $value = $count === 1 ? $segments[0] : $segments[1];
            return $this->makeReplacements(trim($value), array_merge($replace, ['count' => $count]));
        }

        // Range format
        foreach ($segments as $segment) {
            $segment = trim($segment);

            if (preg_match('/^\{(\d+)\}(.+)$/', $segment, $matches)) {
                if ((int) $matches[1] === $count) {
                    return $this->makeReplacements(trim($matches[2]), array_merge($replace, ['count' => $count]));
                }
            }

            if (preg_match('/^\[(\d+),(\*|\d+)\](.+)$/', $segment, $matches)) {
                $min = (int) $matches[1];
                $max = $matches[2] === '*' ? PHP_INT_MAX : (int) $matches[2];

                if ($count >= $min && $count <= $max) {
                    return $this->makeReplacements(trim($matches[3]), array_merge($replace, ['count' => $count]));
                }
            }
        }

        return $this->makeReplacements($segments[0], array_merge($replace, ['count' => $count]));
    }
}