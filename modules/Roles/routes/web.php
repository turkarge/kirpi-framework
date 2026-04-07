<?php

declare(strict_types=1);

/** @var \Core\Routing\Router $router */

$router->get('/roles', [\Modules\Roles\Controllers\RoleManagementController::class, 'index'])
    ->middleware('auth', 'permission:roles.view')
    ->name('roles.index');

$router->post('/roles', [\Modules\Roles\Controllers\RoleManagementController::class, 'store'])
    ->middleware('auth', 'permission:roles.create')
    ->name('roles.store');

$router->get('/roles/matrix', [\Modules\Roles\Controllers\RoleManagementController::class, 'matrix'])
    ->middleware('auth', 'permission:roles.matrix')
    ->name('roles.matrix');

$router->post('/roles/matrix', [\Modules\Roles\Controllers\RoleManagementController::class, 'updateMatrix'])
    ->middleware('auth', 'permission:roles.matrix')
    ->name('roles.matrix.update');

$router->get('/roles/{role}', [\Modules\Roles\Controllers\RoleManagementController::class, 'show'])
    ->middleware('auth', 'permission:roles.view')
    ->name('roles.show');

$router->put('/roles/{role}/status', [\Modules\Roles\Controllers\RoleManagementController::class, 'toggleStatus'])
    ->middleware('auth', 'permission:roles.toggle')
    ->name('roles.status');

$router->get('/roles/{role}/edit', [\Modules\Roles\Controllers\RoleManagementController::class, 'edit'])
    ->middleware('auth', 'permission:roles.update')
    ->name('roles.edit');
