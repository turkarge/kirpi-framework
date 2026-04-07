<?php

declare(strict_types=1);

return [
    'meta_title' => 'Log Viewer',
    'pretitle' => 'Observability',
    'title' => 'System Logs',
    'subtitle' => 'View channel-based log files in a safe and simple interface.',
    'form' => [
        'file' => 'Log File',
        'lines' => 'Line Limit',
        'search' => 'Search',
        'channel' => 'Channel',
        'level' => 'Level',
        'all' => 'All',
    ],
    'actions' => [
        'refresh' => 'Refresh',
        'download' => 'Open Full',
    ],
    'stats' => [
        'title' => 'Summary',
        'rows' => 'Visible Lines',
        'parsed_rows' => 'Parsed Records',
        'size' => 'File Size',
        'updated_at' => 'Updated At',
    ],
    'output' => [
        'title' => 'Log Output',
        'table_title' => 'Table',
        'raw_title' => 'Raw',
        'filtered_rows' => 'Filtered Rows',
    ],
    'table' => [
        'empty' => 'No log files generated yet.',
        'empty_rows' => 'No rows for selected filter.',
        'time' => 'Time',
        'channel' => 'Channel',
        'level' => 'Level',
        'message' => 'Message',
        'request_id' => 'Request ID',
        'path' => 'Path',
        'status' => 'Status',
        'duration_ms' => 'Duration (ms)',
        'user_id' => 'User',
    ],
];
