<?php

declare(strict_types=1);

/** @var \Core\Routing\Router $router */

$router->get('/manager', [\Manager\Http\Controllers\ControlPlaneController::class, 'dashboard']);
$router->get('/manager/core', [\Manager\Http\Controllers\ControlPlaneController::class, 'corePage']);
$router->get('/manager/integrations', [\Manager\Http\Controllers\ControlPlaneController::class, 'integrationsPage']);
$router->get('/manager/developer', [\Manager\Http\Controllers\ControlPlaneController::class, 'developerPage']);
$router->get('/manager/system', [\Manager\Http\Controllers\ControlPlaneController::class, 'systemPage']);
$router->get('/manager/modules', [\Manager\Http\Controllers\ControlPlaneController::class, 'modulesPage']);
$router->get('/manager/custom-modules', [\Manager\Http\Controllers\ControlPlaneController::class, 'customModulesPage']);
$router->get('/manager/mail', [\Manager\Http\Controllers\ControlPlaneController::class, 'mailPage']);
$router->get('/manager/tests', [\Manager\Http\Controllers\ControlPlaneController::class, 'testsPage']);

$router->group([
    'prefix' => '/manager/api',
    'middleware' => ['manager.token'],
], function (\Core\Routing\Router $router): void {
    $router->get('/overview', [\Manager\Http\Controllers\ControlPlaneController::class, 'overview']);
    $router->get('/modules', [\Manager\Http\Controllers\ControlPlaneController::class, 'modules']);
    $router->get('/env', [\Manager\Http\Controllers\ControlPlaneController::class, 'env']);
    $router->get('/generate/module', [\Manager\Http\Controllers\ControlPlaneController::class, 'generateModule']);
    $router->get('/generate/crud', [\Manager\Http\Controllers\ControlPlaneController::class, 'generateCrud']);
    $router->get('/mail/test', [\Manager\Http\Controllers\ControlPlaneController::class, 'mailTest']);
    $router->get('/runtime/ready', [\Manager\Http\Controllers\ControlPlaneController::class, 'runtimeReady']);
    $router->get('/runtime/self-check', [\Manager\Http\Controllers\ControlPlaneController::class, 'runtimeSelfCheck']);
    $router->get('/runtime/history', [\Manager\Http\Controllers\ControlPlaneController::class, 'runtimeHistory']);
});

// Dev Lab pages on manager context
$router->get('/kirpi', [\Core\Runtime\RuntimeController::class, 'dashboard']);
$router->get('/ready', [\Core\Runtime\RuntimeController::class, 'ready']);
$router->get('/health', function (): \Core\Http\Response {
    return \Core\Http\Response::json([
        'status' => 'healthy',
        'timestamp' => date('Y-m-d H:i:s'),
    ]);
});
$router->get('/kirpi/self-check', [\Core\Runtime\RuntimeController::class, 'selfCheck']);
$router->get('/kirpi/self-check/history', [\Core\Runtime\RuntimeController::class, 'selfCheckHistory']);

$router->get('/kirpi/ui-kit', [\Core\Frontend\AdminUiController::class, 'kit']);
$router->get('/kirpi/admin-demo', [\Core\Frontend\AdminUiController::class, 'demo']);
$router->get('/kirpi/notify-test', [\Core\Frontend\AdminUiController::class, 'notifyTest']);
$router->get('/kirpi/api-notify-test', [\Core\Frontend\AdminUiController::class, 'apiNotifyTest']);
$router->get('/kirpi/api-notify-sample', [\Core\Frontend\AdminUiController::class, 'apiNotifySample']);
$router->get('/kirpi/pwa-test', [\Core\Frontend\AdminUiController::class, 'pwaTest']);
$router->get('/kirpi/modal-test', [\Core\Frontend\AdminUiController::class, 'modalTest']);
$router->get('/kirpi/import-export-test', [\Core\Frontend\AdminUiController::class, 'importExportTest']);
$router->get('/kirpi/state-test', [\Core\Frontend\AdminUiController::class, 'stateTest']);
$router->get('/kirpi/a11y-test', [\Core\Frontend\AdminUiController::class, 'a11yTest']);

if ((bool) env('KIRPI_FEATURE_AI', false)) {
    $router->get('/kirpi/ai-sql-test', [\Core\Frontend\AdminUiController::class, 'aiSqlTest']);
    $router->get('/kirpi/api/ai-sql-ask', [\Core\Frontend\AdminUiController::class, 'apiAiSqlAsk']);
}

if ((bool) env('KIRPI_FEATURE_MONITORING', true)) {
    $router->group(['prefix' => '/kirpi-monitor'], function (\Core\Routing\Router $router): void {
        $router->get('/', [\Core\Monitor\MonitorController::class, 'dashboard']);
        $router->get('/api/health', [\Core\Monitor\MonitorController::class, 'health']);
        $router->get('/api/metrics', [\Core\Monitor\MonitorController::class, 'metrics']);
        $router->get('/api/snapshot', [\Core\Monitor\MonitorController::class, 'snapshot']);
        $router->get('/api/logs', [\Core\Monitor\MonitorController::class, 'logs']);
        $router->get('/api/routes', [\Core\Monitor\MonitorController::class, 'routes']);
        $router->get('/api/info', [\Core\Monitor\MonitorController::class, 'info']);
    });
}
