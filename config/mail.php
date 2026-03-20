<?php

declare(strict_types=1);

return [
    'default' => env('MAIL_DRIVER', 'log'),

    'drivers' => [
        'smtp' => [
            'driver'     => 'smtp',
            'host'       => env('MAIL_HOST', 'smtp.gmail.com'),
            'port'       => env('MAIL_PORT', 587),
            'encryption' => env('MAIL_ENCRYPTION', 'tls'),
            'username'   => env('MAIL_USERNAME', ''),
            'password'   => env('MAIL_PASSWORD', ''),
            'timeout'    => 30,
        ],

        // Gmail
        'gmail' => [
            'driver'     => 'smtp',
            'host'       => 'smtp.gmail.com',
            'port'       => 587,
            'encryption' => 'tls',
            'username'   => env('GMAIL_USERNAME', ''),
            'password'   => env('GMAIL_APP_PASSWORD', ''),
            'timeout'    => 30,
        ],

        // Outlook
        'outlook' => [
            'driver'     => 'smtp',
            'host'       => 'smtp-mail.outlook.com',
            'port'       => 587,
            'encryption' => 'tls',
            'username'   => env('OUTLOOK_USERNAME', ''),
            'password'   => env('OUTLOOK_PASSWORD', ''),
            'timeout'    => 30,
        ],

        // Mailgun
        'mailgun' => [
            'driver'   => 'mailgun',
            'api_key'  => env('MAILGUN_API_KEY', ''),
            'domain'   => env('MAILGUN_DOMAIN', ''),
            'endpoint' => env('MAILGUN_ENDPOINT', 'https://api.mailgun.net/v3'),
        ],

        // Log — dev ortamı
        'log' => [
            'driver' => 'log',
        ],
    ],

    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'noreply@kirpi.dev'),
        'name'    => env('MAIL_FROM_NAME', 'Kirpi Framework'),
    ],
];