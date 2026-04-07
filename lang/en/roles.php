<?php

declare(strict_types=1);

return [
    'meta_title' => 'Role Management',
    'pretitle' => 'Authorization',
    'title' => 'Role Management',
    'subtitle' => 'Manage role access scopes in a clean and controlled way.',
    'actions' => [
        'new' => 'New Role',
        'matrix' => 'Permission Matrix',
        'view' => 'View',
        'edit' => 'Edit',
        'clone' => 'Clone',
        'save' => 'Save',
        'delete' => 'Delete',
        'cancel' => 'Cancel',
        'create' => 'Create',
        'back_to_list' => 'Back to Role List',
    ],
    'status' => [
        'active' => 'Active',
        'passive' => 'Passive',
        'active_detail' => 'Assigned to :count users',
        'passive_detail' => 'Access disabled',
    ],
    'filters' => [
        'search' => 'Search role...',
        'all' => 'All statuses',
    ],
    'table' => [
        'title' => 'Role List',
        'role' => 'Role',
        'user_count' => 'Users',
        'status' => 'Status',
        'updated_at' => 'Updated',
        'actions' => 'Actions',
        'empty' => 'No roles found for the selected filter.',
    ],
    'side' => [
        'title' => 'Role Policy',
        'description' => 'Before publishing role changes, validate permission sets and impacted users.',
        'hint' => 'Prefer disabling over deleting for critical roles.',
    ],
    'modal' => [
        'new_title' => 'Create New Role',
    ],
    'form' => [
        'title' => 'Role Detail',
        'name' => 'Role Name',
        'slug' => 'Slug',
        'description' => 'Description',
        'status' => 'Status',
    ],
    'permissions' => [
        'title' => 'Permissions',
    ],
    'audit' => [
        'title' => 'Recent Changes',
        'placeholder' => 'Change logs will be listed here.',
    ],
    'detail' => [
        'meta_title' => 'Role Detail: :role',
        'title' => 'Role View',
        'subtitle' => 'Read-only summary of :role role',
    ],
    'edit' => [
        'meta_title' => 'Edit Role: :role',
        'title' => 'Edit Role',
    ],
    'flash' => [
        'success_title' => 'Success',
        'warning_title' => 'Warning',
        'error_title' => 'Error',
        'created' => 'Role record created.',
        'validation_failed' => 'Please check the role name field.',
        'slug_taken' => 'This slug is already in use.',
        'status_updated' => 'Role status updated.',
        'not_found' => 'Role record not found.',
    ],
    'footer' => [
        'dashboard' => 'Dashboard',
        'terms' => 'Terms of Service',
    ],
];
