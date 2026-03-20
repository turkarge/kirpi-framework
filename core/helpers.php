<?php

declare(strict_types=1);

use Core\Container\Container;

if (!function_exists('app')) {
    function app(?string $abstract = null, array $parameters = []): mixed
    {
        $container = Container::getInstance();

        if ($abstract === null) {
            return $container;
        }

        return $container->make($abstract, $parameters);
    }
}

if (!function_exists('base_path')) {
    function base_path(string $path = ''): string
    {
        return BASE_PATH . ($path ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : '');
    }
}

if (!function_exists('storage_path')) {
    function storage_path(string $path = ''): string
    {
        return base_path('storage') . ($path ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : '');
    }
}

if (!function_exists('config_path')) {
    function config_path(string $path = ''): string
    {
        return base_path('config') . ($path ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : '');
    }
}

if (!function_exists('public_path')) {
    function public_path(string $path = ''): string
    {
        return base_path('public') . ($path ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : '');
    }
}

if (!function_exists('now')) {
    function now(): string
    {
        return date('Y-m-d H:i:s');
    }
}

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        $value = $_ENV[$key] ?? getenv($key);

        if ($value === false) {
            return $default;
        }

        return match(strtolower($value)) {
            'true', '(true)'   => true,
            'false', '(false)' => false,
            'null', '(null)'   => null,
            'empty', '(empty)' => '',
            default            => $value,
        };
    }
}

if (!function_exists('config')) {
    function config(string $key, mixed $default = null): mixed
    {
        return app('config')->get($key, $default);
    }
}

if (!function_exists('class_basename')) {
    function class_basename(string|object $class): string
    {
        $class = is_object($class) ? get_class($class) : $class;
        return basename(str_replace('\\', '/', $class));
    }
}

if (!function_exists('data_get')) {
    function data_get(mixed $target, string $key, mixed $default = null): mixed
    {
        $keys = explode('.', $key);

        foreach ($keys as $segment) {
            if (is_array($target) && array_key_exists($segment, $target)) {
                $target = $target[$segment];
            } elseif (is_object($target) && isset($target->$segment)) {
                $target = $target->$segment;
            } else {
                return $default;
            }
        }

        return $target;
    }
}

if (!function_exists('response')) {
    function response(string $content = '', int $status = 200, array $headers = []): \Core\Http\Response
    {
        return \Core\Http\Response::make($content, $status, $headers);
    }
}

if (!function_exists('redirect')) {
    function redirect(string $url, int $status = 302): \Core\Http\Response
    {
        return \Core\Http\Response::redirect($url, $status);
    }
}

if (!function_exists('back')) {
    function back(): \Core\Http\Response
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        return \Core\Http\Response::redirect($referer);
    }
}

if (!function_exists('abort')) {
    function abort(int $status, string $message = ''): never
    {
        throw new \Core\Exception\HttpException($status, $message);
    }
}

if (!function_exists('cache')) {
    function cache(?string $key = null, mixed $default = null): mixed
    {
        $manager = app(\Core\Cache\CacheManager::class);

        if ($key === null) {
            return $manager;
        }

        return $manager->get($key, $default);
    }
}

if (!function_exists('event')) {
    function event(\Core\Event\Event|string $event, array $payload = []): void
    {
        app(\Core\Event\EventDispatcher::class)->dispatch($event, $payload);
    }
}