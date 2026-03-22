<?php

declare(strict_types=1);

return [
    // AI cekirdek seviyede opsiyoneldir. Varsayilan kapali.
    'enabled' => env('KIRPI_FEATURE_AI', false),

    // Gelecekte openai/anthropic/yerel adapter secimi icin.
    'default' => env('AI_PROVIDER', 'null'),

    'providers' => [
        'null' => [
            'driver' => 'null',
        ],
    ],
];
