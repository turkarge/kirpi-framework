<?php

declare(strict_types=1);

return [
    'default' => env('DB_CONNECTION', 'mysql'),

    'connections' => [
        'mysql' => [
            'driver'   => 'mysql',
            'host'     => env('DB_HOST', '127.0.0.1'),
            'port'     => env('DB_PORT', 3306),
            'database' => env('DB_DATABASE', 'kirpi'),
            'username' => env('DB_USERNAME', 'kirpi'),
            'password' => env('DB_PASSWORD', ''),
            'charset'  => 'utf8mb4',
            'options'  => [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ],
        ],

        'pgsql' => [
            'driver'   => 'pgsql',
            'host'     => env('PGSQL_HOST', '127.0.0.1'),
            'port'     => env('PGSQL_PORT', 5432),
            'database' => env('PGSQL_DATABASE', 'kirpi'),
            'username' => env('PGSQL_USERNAME', 'kirpi'),
            'password' => env('PGSQL_PASSWORD', ''),
            'schema'   => 'public',
        ],

        'sqlite' => [
            'driver'   => 'sqlite',
            'database' => env('SQLITE_DATABASE', storage_path('database.sqlite')),
        ],

        'redis' => [
            'driver'   => 'redis',
            'host'     => env('REDIS_HOST', '127.0.0.1'),
            'port'     => env('REDIS_PORT', 6379),
            'password' => env('REDIS_PASSWORD', null),
            'database' => env('REDIS_DB', 0),
        ],
    ],
];