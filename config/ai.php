<?php

declare(strict_types=1);

return [
    // AI cekirdek seviyede opsiyoneldir. Varsayilan kapali.
    'enabled' => env('KIRPI_FEATURE_AI', false),

    // Provider secimi: null, ollama
    'default' => env('AI_PROVIDER', 'null'),
    'model' => env('AI_MODEL', 'qwen2.5-coder:3b'),
    'test_models' => [
        'qwen2.5-coder:1.5b',
        'qwen2.5-coder:3b',
        'qwen2.5-coder:7b',
        'mannix/defog-llama3-sqlcoder-8b',
    ],

    'providers' => [
        'null' => [
            'driver' => 'null',
        ],
        'ollama' => [
            'driver' => 'ollama',
            'base_url' => env('AI_OLLAMA_BASE_URL', 'http://ollama:11434'),
            'model' => env('AI_MODEL', 'qwen2.5-coder:3b'),
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
