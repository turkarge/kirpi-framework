<?php

declare(strict_types=1);

/** @var \Core\Routing\Router $router */

$router->get('/manager', [\Manager\Http\Controllers\ControlPlaneController::class, 'dashboard']);

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
});

