<?php

declare(strict_types=1);

namespace Manager\Http\Controllers;

use Core\Http\Response;
use Core\Routing\Router;
use Core\Runtime\RuntimeDiagnostics;

class ControlPlaneController
{
    public function overview(): Response
    {
        /** @var Router $router */
        $router = app(Router::class);
        $routes = $router->getRoutes()->all();

        return Response::json([
            'ok' => true,
            'data' => [
                'context' => (string) env('APP_CONTEXT', 'manager'),
                'env' => (string) env('APP_ENV', 'local'),
                'routes_total' => count($routes),
                'communication_enabled' => (bool) env('KIRPI_FEATURE_COMMUNICATION', true),
                'api_alive' => true,
                'timestamp' => date('Y-m-d H:i:s'),
            ],
        ]);
    }

    public function health(): Response
    {
        return Response::json([
            'status' => 'healthy',
            'timestamp' => date('Y-m-d H:i:s'),
            'context' => 'manager',
        ]);
    }

    public function ready(): Response
    {
        /** @var RuntimeDiagnostics $diagnostics */
        $diagnostics = app(RuntimeDiagnostics::class);
        $payload = $diagnostics->readinessPayload();
        $status = (string) ($payload['status'] ?? 'degraded');

        return Response::json($payload, $status === 'healthy' ? 200 : 503);
    }
}
