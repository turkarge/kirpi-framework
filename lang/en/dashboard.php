<?php

declare(strict_types=1);

return [
    'meta_title' => 'Core Dashboard',
    'title' => 'Core Dashboard',
    'subtitle' => 'Default checkpoint after authentication.',
    'welcome' => 'Welcome, :name',
    'description' => ':app core is ready. You can add app-specific modules with make:module and make:crud commands.',
    'account_summary' => 'Account Summary',
    'next_steps' => 'Next Steps',
    'actions' => [
        'health' => 'Health',
        'ready' => 'Ready',
    ],
    'metrics' => [
        'routes' => 'Routes',
        'routes_note' => 'Core system endpoints are active.',
        'modules' => 'Modules',
        'modules_note' => 'Loaded module directories count.',
        'database' => 'Database',
        'cache' => 'Cache',
    ],
    'fields' => [
        'users_total' => 'Users',
        'roles_total' => 'Roles',
        'modules_total' => 'Modules',
    ],
    'status' => [
        'up' => 'UP',
        'down' => 'DOWN',
        'na' => '-',
        'latency_up' => 'Healthy ( :ms ms )',
        'latency_down' => 'Unavailable ( :ms ms )',
    ],
    'table' => [
        'step_col' => 'Step',
        'status_col' => 'Status',
        'note_col' => 'Note',
        'ready' => 'Ready',
        'pending' => 'Pending',
        'step_module' => 'Create your first application module skeleton.',
        'step_crud' => 'Create your first admin CRUD flow.',
        'step_security' => 'Complete role/permission and security baseline settings.',
        'detail_ok' => 'Check completed.',
        'detail_module_pending' => 'Current module count: :count',
        'detail_crud_pending' => 'Users: :users, Roles: :roles',
        'detail_security_pending' => 'DB: :db, Cache: :cache, Roles: :roles',
    ],
];
