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
    'providers' => [
        Core\Providers\FoundationServiceProvider::class,
        Core\Providers\DatabaseServiceProvider::class,
        Core\Providers\AuthServiceProvider::class,
        Core\Providers\SupportServiceProvider::class,
        Core\Providers\CommunicationServiceProvider::class,
        Core\Providers\MonitoringServiceProvider::class,
        Core\Providers\RoutingServiceProvider::class,
    ],
];
