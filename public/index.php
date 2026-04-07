<?php

declare(strict_types=1);

define('KIRPI_START', microtime(true));
define('BASE_PATH', dirname(__DIR__));

require BASE_PATH . '/vendor/autoload.php';

$app = require BASE_PATH . '/bootstrap/app.php';

/** @var \Core\Routing\Router $router */
$router = $app->make(\Core\Routing\Router::class);

$context = (string) env('APP_CONTEXT', 'app');
if ($context === 'manager') {
    if (is_file(BASE_PATH . '/routes/manager.php')) {
        $router->loadRoutes(BASE_PATH . '/routes/manager.php');
    }
} else {
    if (is_file(BASE_PATH . '/routes/web.php')) {
        $router->loadRoutes(BASE_PATH . '/routes/web.php', ['middleware' => 'web']);
    }

    if (is_file(BASE_PATH . '/routes/api.php')) {
        $router->loadRoutes(BASE_PATH . '/routes/api.php', ['middleware' => 'api']);
    }
}

$request = \Core\Http\Request::capture();
$requestId = trim((string) $request->header('X-Request-Id', ''));
if ($requestId === '') {
    $requestId = bin2hex(random_bytes(8));
}
\Core\Support\RequestContext::setRequestId($requestId);

$response = $router->dispatch($request);
$response = $response->header('X-Request-Id', $requestId);

try {
    $config = app('config')->load('logging');
    $requestConfig = is_array($config['request_logging'] ?? null) ? $config['request_logging'] : [];
    $enabled = (bool) ($requestConfig['enabled'] ?? true);
    $skipPaths = is_array($requestConfig['skip_paths'] ?? null) ? $requestConfig['skip_paths'] : [];
    if ($enabled && !in_array($request->path(), $skipPaths, true)) {
        $userId = null;
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (isset($_SESSION['auth_id'])) {
            $userId = (int) $_SESSION['auth_id'];
        }

        $durationMs = round((microtime(true) - KIRPI_START) * 1000, 2);
        app(\Core\Logging\Logger::class)->channel('request')->info('request.completed', [
            'request_id' => $requestId,
            'method' => $request->method(),
            'path' => $request->path(),
            'status' => $response->getStatus(),
            'duration_ms' => $durationMs,
            'ip' => $request->ip(),
            'user_id' => $userId,
            'user_agent' => $request->userAgent(),
        ]);
    }
} catch (\Throwable) {
    // Request logging must never break response flow.
}

$response->send();
