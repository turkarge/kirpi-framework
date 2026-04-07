<?php

declare(strict_types=1);

/** @var \Core\Routing\Router $router */

$router->get('/users', [\Modules\Users\Controllers\UserManagementController::class, 'index'])
    ->middleware('auth')
    ->name('users.index');

$router->post('/users', [\Modules\Users\Controllers\UserManagementController::class, 'store'])
    ->middleware('auth')
    ->name('users.store');

$router->put('/users/{id}/status', [\Modules\Users\Controllers\UserManagementController::class, 'toggleStatus'])
    ->middleware('auth')
    ->name('users.status');

$router->get('/users/{id}', [\Modules\Users\Controllers\UserManagementController::class, 'show'])
    ->middleware('auth')
    ->name('users.show');

$router->get('/users/{id}/edit', [\Modules\Users\Controllers\UserManagementController::class, 'edit'])
    ->middleware('auth')
    ->name('users.edit');

$router->put('/users/{id}', [\Modules\Users\Controllers\UserManagementController::class, 'update'])
    ->middleware('auth')
    ->name('users.update');

$router->get('/locales', [\Modules\Users\Controllers\LocaleManagementController::class, 'index'])
    ->middleware('auth')
    ->name('locales.index');

$router->put('/locales', [\Modules\Users\Controllers\LocaleManagementController::class, 'update'])
    ->middleware('auth')
    ->name('locales.update');

$router->put('/locales/translations', [\Modules\Users\Controllers\LocaleManagementController::class, 'updateTranslations'])
    ->middleware('auth')
    ->name('locales.translations.update');
