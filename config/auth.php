<?php

declare(strict_types=1);

return [
    'default' => env('AUTH_GUARD', 'session'),

    'guards' => [
        'session' => [
            'driver'   => 'session',
            'provider' => 'users',
        ],
        'api' => [
            'driver'   => 'jwt',
            'provider' => 'users',
            'ttl'      => (int) env('JWT_TTL', 3600),
            'refresh'  => (int) env('JWT_REFRESH', 604800),
        ],
    ],

    'providers' => [
        'users' => [
            'driver' => 'database',
            'table'  => 'users',
            'model'  => \Modules\Users\Models\User::class,
        ],
    ],

    'password' => [
        'algorithm' => PASSWORD_ARGON2ID,
        'options'   => [
            'memory_cost' => 65536,
            'time_cost'   => 4,
            'threads'     => 3,
        ],
    ],

    'throttle' => [
        'max_attempts'  => 5,
        'decay_seconds' => 300,
    ],
];