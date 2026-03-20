<?php

declare(strict_types=1);

define('KIRPI_START', microtime(true));
define('BASE_PATH', dirname(__DIR__));

// TEMP DEBUG
if (isset($_GET['debug'])) {
    define('BASE_PATH', dirname(__DIR__));
    require BASE_PATH . '/vendor/autoload.php';
    $app = require BASE_PATH . '/bootstrap/app.php';
    $db = $app->make(\Core\Database\DatabaseManager::class);
    try {
        $db->connection()->statement('CREATE TABLE test123 (id INT)');
        echo 'Table created OK';
    } catch(\Exception $e) {
        echo 'ERROR: ' . $e->getMessage();
    }
    exit;
}

require BASE_PATH . '/vendor/autoload.php';

$app = require BASE_PATH . '/bootstrap/app.php';

// Router'ı yükle
$router = $app->make(\Core\Routing\Router::class);

// Route'ları yükle
if (file_exists(BASE_PATH . '/routes/web.php')) {
    $router->loadRoutes(BASE_PATH . '/routes/web.php');
}

if (file_exists(BASE_PATH . '/routes/api.php')) {
    $router->loadRoutes(BASE_PATH . '/routes/api.php');
}

// Request yakala ve dispatch et
$request  = \Core\Http\Request::capture();
$response = $router->dispatch($request);
$response->send();