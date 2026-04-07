<?php

declare(strict_types=1);

/** @var \Core\Routing\Router $router */

$router->get('/users', [\Modules\Users\Controllers\UserManagementController::class, 'index'])
    ->middleware('auth', 'permission:users.view')
    ->name('users.index');

$router->post('/users', [\Modules\Users\Controllers\UserManagementController::class, 'store'])
    ->middleware('auth', 'permission:users.create')
    ->name('users.store');

$router->put('/users/{id}/status', [\Modules\Users\Controllers\UserManagementController::class, 'toggleStatus'])
    ->middleware('auth', 'permission:users.toggle')
    ->name('users.status');

$router->get('/users/{id}', [\Modules\Users\Controllers\UserManagementController::class, 'show'])
    ->middleware('auth', 'permission:users.view')
    ->name('users.show');

$router->get('/users/{id}/edit', [\Modules\Users\Controllers\UserManagementController::class, 'edit'])
    ->middleware('auth', 'permission:users.update')
    ->name('users.edit');

$router->put('/users/{id}', [\Modules\Users\Controllers\UserManagementController::class, 'update'])
    ->middleware('auth', 'permission:users.update')
    ->name('users.update');

$router->get('/locales', [\Modules\Users\Controllers\LocaleManagementController::class, 'index'])
    ->middleware('auth', 'permission:locales.view')
    ->name('locales.index');

$router->put('/locales', [\Modules\Users\Controllers\LocaleManagementController::class, 'update'])
    ->middleware('auth', 'permission:locales.update')
    ->name('locales.update');

$router->put('/locales/translations', [\Modules\Users\Controllers\LocaleManagementController::class, 'updateTranslations'])
    ->middleware('auth', 'permission:locales.update')
    ->name('locales.translations.update');
