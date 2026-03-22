<?php

declare(strict_types=1);

return [
    // AI cekirdek seviyede opsiyoneldir. Varsayilan kapali.
    'enabled' => env('KIRPI_FEATURE_AI', false),

    // Provider secimi: null, openai, anthropic
    'default' => env('AI_PROVIDER', 'null'),
    'model' => env('AI_MODEL', 'gpt-4.1-mini'),
    'test_models' => [
        'gpt-4.1-mini',
        'gpt-4.1',
        'claude-3-5-haiku-latest',
        'claude-3-7-sonnet-latest',
    ],

    'providers' => [
        'null' => [
            'driver' => 'null',
        ],
        'openai' => [
            'driver' => 'openai',
            'base_url' => env('AI_OPENAI_BASE_URL', 'https://api.openai.com/v1'),
            'api_key' => env('AI_OPENAI_API_KEY', ''),
            'model' => env('AI_OPENAI_MODEL', env('AI_MODEL', 'gpt-4.1-mini')),
            'timeout' => (int) env('AI_TIMEOUT', 60),
        ],
        'anthropic' => [
            'driver' => 'anthropic',
            'base_url' => env('AI_ANTHROPIC_BASE_URL', 'https://api.anthropic.com/v1'),
            'api_key' => env('AI_ANTHROPIC_API_KEY', ''),
            'model' => env('AI_ANTHROPIC_MODEL', env('AI_MODEL', 'claude-3-5-haiku-latest')),
            'timeout' => (int) env('AI_TIMEOUT', 60),
        ],
    ],

    'sql' => [
        'max_rows' => (int) env('AI_SQL_MAX_ROWS', 200),
        'default_limit' => (int) env('AI_SQL_DEFAULT_LIMIT', 100),
        'max_cell_length' => (int) env('AI_SQL_MAX_CELL_LENGTH', 280),
        'allow_tables' => env('AI_SQL_ALLOW_TABLES', '*'),
        'deny_keywords' => [
            'insert', 'update', 'delete', 'drop', 'alter', 'truncate',
            'create', 'replace', 'grant', 'revoke', 'call', 'execute',
            'into outfile', 'load_file', 'attach database', 'pragma',
        ],
    ],

    'trace' => [
        'enabled' => env('AI_TRACE_ENABLED', false),
    ],
];
