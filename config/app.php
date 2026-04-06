<?php

declare(strict_types=1);

return [
    'name'     => env('APP_NAME', 'Kirpi Framework'),
    'logo'     => env('KIRPI_APP_LOGO', 'https://s3.kirpinetwork.com/web/kirpi.svg'),
    'env'      => env('APP_ENV', 'production'),
    'debug'    => env('APP_DEBUG', false),
    'url'      => env('APP_URL', 'http://localhost'),
    'key'      => env('APP_KEY', ''),
    'datetime_format' => env('APP_DATETIME_FORMAT', 'd.m.Y H:i'),
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
        env('KIRPI_FEATURE_AI', false) ? Core\Providers\AiServiceProvider::class : null,
        Core\Providers\RoutingServiceProvider::class,
    ], static fn(mixed $provider): bool => is_string($provider))),
];
