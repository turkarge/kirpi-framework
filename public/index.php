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
$response = $router->dispatch($request);
$response->send();

