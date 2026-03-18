<?php

declare(strict_types=1);

namespace Core\Config;

class EnvLoader
{
    private static bool $loaded = false;

    public static function load(string $path): void
    {
        if (static::$loaded) {
            return;
        }

        $file = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '.env';

        if (!file_exists($file)) {
            return;
        }

        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            // Yorum satırlarını atla
            if (str_starts_with(trim($line), '#')) {
                continue;
            }

            // KEY=VALUE formatını parse et
            if (!str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);

            $key   = trim($key);
            $value = trim($value);

            // Tırnak işaretlerini temizle
            $value = static::stripQuotes($value);

            // Inline yorum temizle
            $value = static::stripInlineComment($value);

            if (!array_key_exists($key, $_ENV)) {
                $_ENV[$key]    = $value;
                $_SERVER[$key] = $value;
                putenv("{$key}={$value}");
            }
        }

        static::$loaded = true;
    }

    public static function reload(string $path): void
    {
        static::$loaded = false;
        static::load($path);
    }

    private static function stripQuotes(string $value): string
    {
        if (strlen($value) > 1) {
            $first = $value[0];
            $last  = $value[-1];

            if (($first === '"' && $last === '"') ||
                ($first === "'" && $last === "'")) {
                return substr($value, 1, -1);
            }
        }

        return $value;
    }

    private static function stripInlineComment(string $value): string
    {
        // Tırnaksız değerlerde # ile başlayan yorumu temizle
        if (!str_starts_with($value, '"') && !str_starts_with($value, "'")) {
            if (($pos = strpos($value, ' #')) !== false) {
                $value = trim(substr($value, 0, $pos));
            }
        }

        return $value;
    }
}