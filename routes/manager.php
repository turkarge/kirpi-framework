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

if ((bool) env('KIRPI_FEATURE_MONITORING', true)) {
    $router->group(['prefix' => '/monitor'], function (\Core\Routing\Router $router): void {
        $router->get('/', [\Core\Monitor\MonitorController::class, 'dashboard']);
        $router->get('/api/health', [\Core\Monitor\MonitorController::class, 'health']);
        $router->get('/api/metrics', [\Core\Monitor\MonitorController::class, 'metrics']);
        $router->get('/api/snapshot', [\Core\Monitor\MonitorController::class, 'snapshot']);
        $router->get('/api/logs', [\Core\Monitor\MonitorController::class, 'logs']);
        $router->get('/api/routes', [\Core\Monitor\MonitorController::class, 'routes']);
        $router->get('/api/info', [\Core\Monitor\MonitorController::class, 'info']);
    });
}

