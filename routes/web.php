<?php

declare(strict_types=1);

/** @var \Core\Routing\Router $router */

$router->get('/', function (): \Core\Http\Response {
    return \Core\Http\Response::json([
        'framework' => 'Kirpi Framework',
        'version' => '1.0.0',
        'php' => PHP_VERSION,
        'env' => env('APP_ENV', 'local'),
        'status' => 'running',
        'time' => round((microtime(true) - KIRPI_START) * 1000, 2) . 'ms',
    ]);
});

$router->get('/health', function (): \Core\Http\Response {
    return \Core\Http\Response::json([
        'status' => 'healthy',
        'timestamp' => date('Y-m-d H:i:s'),
    ]);
});

$router->get('/ready', [\Core\Runtime\RuntimeController::class, 'ready']);
$router->get('/kirpi/self-check', [\Core\Runtime\RuntimeController::class, 'selfCheck']);
$router->get('/kirpi/self-check/history', [\Core\Runtime\RuntimeController::class, 'selfCheckHistory']);
$router->get('/kirpi', [\Core\Runtime\RuntimeController::class, 'dashboard']);
$router->get('/kirpi/ui-kit', [\Core\Frontend\AdminUiController::class, 'kit']);
$router->get('/kirpi/admin-demo', [\Core\Frontend\AdminUiController::class, 'demo']);

if ((bool) env('KIRPI_FEATURE_MONITORING', true)) {
    $router->group(['prefix' => '/kirpi-monitor'], function (\Core\Routing\Router $router): void {
        $router->get('/', [\Core\Monitor\MonitorController::class, 'dashboard']);
        $router->get('/api/health', [\Core\Monitor\MonitorController::class, 'health']);
        $router->get('/api/metrics', [\Core\Monitor\MonitorController::class, 'metrics']);
        $router->get('/api/logs', [\Core\Monitor\MonitorController::class, 'logs']);
        $router->get('/api/routes', [\Core\Monitor\MonitorController::class, 'routes']);
        $router->get('/api/info', [\Core\Monitor\MonitorController::class, 'info']);
    });
}
