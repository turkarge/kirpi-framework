<?php

declare(strict_types=1);

/** @var \Core\Routing\Router $router */

$router->get('/dashboard', [\Modules\Dashboard\Controllers\DashboardController::class, 'index'])
    ->middleware('auth', 'permission:dashboard.view')
    ->name('dashboard.index');
