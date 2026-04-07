<?php

declare(strict_types=1);

return [
    'path' => storage_path('logs'),
    'level' => env('LOG_LEVEL', env('APP_ENV', 'local') === 'local' ? 'DEBUG' : 'INFO'),
    'format' => env('LOG_FORMAT', 'json'), // json|line
    'request_logging' => [
        'enabled' => env('LOG_REQUESTS', true),
        'skip_paths' => [
            '/health',
        ],
    ],
    'redact_keys' => [
        'password',
        'password_confirmation',
        'pin',
        'pin_confirmation',
        'token',
        'access_token',
        'refresh_token',
        'api_key',
        'secret',
        'authorization',
        'cookie',
        'set-cookie',
        'jwt',
        'app_key',
        'mail_password',
    ],
    'channels' => [
        'app' => ['format' => env('LOG_FORMAT', 'json')],
        'auth' => ['format' => env('LOG_FORMAT', 'json')],
        'security' => ['format' => env('LOG_FORMAT', 'json')],
        'db' => ['format' => env('LOG_FORMAT', 'json')],
        'mail' => ['format' => env('LOG_FORMAT', 'json')],
        'queue' => ['format' => env('LOG_FORMAT', 'json')],
        'audit' => ['format' => env('LOG_FORMAT', 'json')],
        'request' => ['format' => env('LOG_FORMAT', 'json')],
        'ai-trace' => ['format' => env('LOG_FORMAT', 'json')],
    ],
];

