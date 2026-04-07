<?php

declare(strict_types=1);

/** @var \Core\Routing\Router $router */

$router->get('/roles', [\Modules\Roles\Controllers\RoleManagementController::class, 'index'])
    ->middleware('auth')
    ->name('roles.index');

$router->post('/roles', [\Modules\Roles\Controllers\RoleManagementController::class, 'store'])
    ->middleware('auth')
    ->name('roles.store');

$router->get('/roles/matrix', [\Modules\Roles\Controllers\RoleManagementController::class, 'matrix'])
    ->middleware('auth')
    ->name('roles.matrix');

$router->post('/roles/matrix', [\Modules\Roles\Controllers\RoleManagementController::class, 'updateMatrix'])
    ->middleware('auth')
    ->name('roles.matrix.update');

$router->get('/roles/{role}', [\Modules\Roles\Controllers\RoleManagementController::class, 'show'])
    ->middleware('auth')
    ->name('roles.show');

$router->put('/roles/{role}/status', [\Modules\Roles\Controllers\RoleManagementController::class, 'toggleStatus'])
    ->middleware('auth')
    ->name('roles.status');

$router->get('/roles/{role}/edit', [\Modules\Roles\Controllers\RoleManagementController::class, 'edit'])
    ->middleware('auth')
    ->name('roles.edit');
