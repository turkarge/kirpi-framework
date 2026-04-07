<?php

declare(strict_types=1);

return [
    'meta_title' => 'Locale Management',
    'pretitle' => 'Management',
    'title' => 'Locale Management',
    'subtitle' => 'Manage default locale and active locales from a single place.',
    'actions' => [
        'save' => 'Save',
        'edit_locale' => 'Edit Locale',
        'save_translations' => 'Save Translations',
    ],
    'card' => [
        'title' => 'Locale Settings',
        'description' => 'These settings are written to .env and used application-wide.',
    ],
    'form' => [
        'default_locale' => 'Default Locale',
        'enabled_locales' => 'Enabled Locales',
        'edit_locale' => 'Locale to Edit',
        'group' => 'File (Group)',
        'filter' => 'Filter by key or text...',
    ],
    'translations' => [
        'title' => 'Quick Translation',
        'description' => 'Pick locale and file group, then update translations row by row.',
    ],
    'table' => [
        'key' => 'Key',
        'target' => 'Text',
        'empty' => 'No translation keys found in selected file.',
    ],
    'current' => [
        'title' => 'Current Configuration',
        'default_locale' => 'Default',
        'enabled_locales' => 'Enabled Locales',
    ],
    'flash' => [
        'success_title' => 'Success',
        'warning_title' => 'Warning',
        'updated' => 'Locale settings updated.',
        'translations_updated' => 'Translation file updated.',
        'invalid_default' => 'Selected default locale is not supported.',
        'invalid_locale' => 'Selected locale is invalid.',
        'invalid_group' => 'Invalid selected locale file group.',
        'empty_enabled' => 'At least one enabled locale must be selected.',
    ],
    'footer' => [
        'dashboard' => 'Dashboard',
        'terms' => 'Terms of Service',
    ],
];
