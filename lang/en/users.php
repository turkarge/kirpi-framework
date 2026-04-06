<?php

declare(strict_types=1);

return [
    'meta_title' => 'User Management',
    'pretitle' => 'Management',
    'title' => 'User Management',
    'subtitle' => 'List users, track their status, and control management flows from a single screen.',
    'actions' => [
        'new' => 'New User',
        'edit' => 'Edit',
        'detail' => 'Detail',
        'create' => 'Create',
        'cancel' => 'Cancel',
        'save' => 'Save',
        'back_to_list' => 'Back to User List',
    ],
    'status' => [
        'active' => 'Active',
        'passive' => 'Passive',
    ],
    'filters' => [
        'search' => 'Search user...',
        'all' => 'All statuses',
    ],
    'table' => [
        'title' => 'User List',
        'name' => 'Full Name',
        'email' => 'Email',
        'last_login_at' => 'Last Login',
        'updated_at' => 'Updated',
        'status' => 'Status',
        'empty' => 'No users found for the selected filter.',
    ],
    'form' => [
        'name' => 'Full Name',
        'email' => 'Email',
        'password' => 'Password',
        'locale' => 'Locale',
        'status' => 'Status',
    ],
    'modal' => [
        'new_title' => 'Create New User',
    ],
    'detail' => [
        'meta_title' => 'User Detail: :name',
        'title' => 'User View',
        'subtitle' => 'Read-only summary of :name user',
        'info_title' => 'Account Info',
    ],
    'edit' => [
        'meta_title' => 'Edit User: :name',
        'title' => 'Edit User',
    ],
    'flash' => [
        'success_title' => 'Success',
        'warning_title' => 'Warning',
        'error_title' => 'Error',
        'updated' => 'User record updated.',
        'created' => 'User record created.',
        'validation_failed' => 'Please check name, email and status fields.',
        'create_validation_failed' => 'Please check name, email and password fields. (Password min: 6)',
        'email_taken' => 'This email is already used by another user.',
        'not_found' => 'User record not found.',
    ],
    'side' => [
        'title' => 'Usage Note',
        'description' => 'User list is currently managed in a simple way with active/passive filter and search.',
        'hint' => 'User edit and detail pages will be completed in the next iteration.',
    ],
    'footer' => [
        'dashboard' => 'Dashboard',
        'terms' => 'Terms of Service',
    ],
];
