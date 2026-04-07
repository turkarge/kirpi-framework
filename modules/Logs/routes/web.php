<?php

declare(strict_types=1);

/** @var \Core\Routing\Router $router */

$router->get('/logs', [\Modules\Logs\Controllers\LogViewerController::class, 'index'])
    ->middleware('auth', 'permission:logs.view')
    ->name('logs.index');

