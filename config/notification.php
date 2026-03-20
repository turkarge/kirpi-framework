<?php

declare(strict_types=1);

return [
    'channels' => [
        'fcm' => [
            'server_key' => env('FCM_SERVER_KEY', ''),
        ],

        'onesignal' => [
            'app_id'  => env('ONESIGNAL_APP_ID', ''),
            'api_key' => env('ONESIGNAL_API_KEY', ''),
        ],
    ],
];