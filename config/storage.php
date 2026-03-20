<?php

declare(strict_types=1);

return [
    'default' => env('STORAGE_DRIVER', 'local'),

    'disks' => [
        'local' => [
            'driver' => 'local',
            'root'   => storage_path('app'),
            'url'    => env('APP_URL') . '/storage',
        ],

        'public' => [
            'driver' => 'local',
            'root'   => base_path('public/storage'),
            'url'    => env('APP_URL') . '/storage',
        ],

        's3' => [
            'driver'   => 's3',
            'key'      => env('AWS_ACCESS_KEY_ID', ''),
            'secret'   => env('AWS_SECRET_ACCESS_KEY', ''),
            'region'   => env('AWS_DEFAULT_REGION', 'eu-central-1'),
            'bucket'   => env('AWS_BUCKET', ''),
            'cdn_url'  => env('AWS_CDN_URL', ''),
        ],

        // Cloudflare R2 — S3 uyumlu
        'r2' => [
            'driver'   => 's3',
            'key'      => env('R2_ACCESS_KEY', ''),
            'secret'   => env('R2_SECRET_KEY', ''),
            'bucket'   => env('R2_BUCKET', ''),
            'endpoint' => env('R2_ENDPOINT', ''),
            'region'   => 'auto',
        ],

        // MinIO — Self-hosted S3
        'minio' => [
            'driver'   => 's3',
            'key'      => env('MINIO_ACCESS_KEY', ''),
            'secret'   => env('MINIO_SECRET_KEY', ''),
            'bucket'   => env('MINIO_BUCKET', ''),
            'endpoint' => env('MINIO_ENDPOINT', 'http://localhost:9000'),
            'region'   => 'us-east-1',
        ],
    ],
];