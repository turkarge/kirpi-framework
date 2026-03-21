<?php

declare(strict_types=1);

return [
    'name'     => env('APP_NAME', 'Kirpi Framework'),
    'env'      => env('APP_ENV', 'production'),
    'debug'    => env('APP_DEBUG', false),
    'url'      => env('APP_URL', 'http://localhost'),
    'key'      => env('APP_KEY', ''),
    'timezone' => env('APP_TIMEZONE', 'Europe/Istanbul'),
    'locale'   => env('APP_LOCALE', 'tr'),
    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),
    'version'  => '1.0.0',
    'providers' => array_values(array_filter([
        Core\Providers\FoundationServiceProvider::class,
        Core\Providers\DatabaseServiceProvider::class,
        Core\Providers\AuthServiceProvider::class,
        Core\Providers\SupportServiceProvider::class,
        env('KIRPI_FEATURE_COMMUNICATION', true) ? Core\Providers\CommunicationServiceProvider::class : null,
        env('KIRPI_FEATURE_MONITORING', true) ? Core\Providers\MonitoringServiceProvider::class : null,
        Core\Providers\RoutingServiceProvider::class,
    ], static fn(mixed $provider): bool => is_string($provider))),
];
