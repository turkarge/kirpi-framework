<?php

declare(strict_types=1);

return [
    'default' => env('CACHE_DRIVER', 'file'),

    'drivers' => [
        'redis' => [
            'driver'     => 'redis',
            'connection' => 'redis',
            'prefix'     => env('CACHE_PREFIX', 'kirpi_cache'),
        ],
        'file' => [
            'driver' => 'file',
            'path'   => storage_path('framework/cache'),
        ],
        'array' => [
            'driver' => 'array',
        ],
    ],

    'ttl' => env('CACHE_TTL', 3600),
];