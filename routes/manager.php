<?php

declare(strict_types=1);

/** @var \Core\Routing\Router $router */

$router->get('/manager', [\Manager\Http\Controllers\ControlPlaneController::class, 'dashboard']);

$router->group([
    'prefix' => '/manager/api',
    'middleware' => ['manager.token', 'throttle:120,60'],
], function (\Core\Routing\Router $router): void {
    $router->get('/overview', [\Manager\Http\Controllers\ControlPlaneController::class, 'overview']);
    $router->get('/health', [\Manager\Http\Controllers\ControlPlaneController::class, 'health']);
    $router->get('/ready', [\Manager\Http\Controllers\ControlPlaneController::class, 'ready']);
});
