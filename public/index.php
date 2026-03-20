<?php

declare(strict_types=1);

define('KIRPI_START', microtime(true));
define('BASE_PATH', dirname(__DIR__));

if (isset($_GET['dbcheck'])) {
    define('BASE_PATH', dirname(__DIR__));
    require BASE_PATH . '/vendor/autoload.php';
    $app = require BASE_PATH . '/bootstrap/app.php';
    echo 'DB: ' . env('DB_DATABASE') . '<br>';
    echo 'Host: ' . env('DB_HOST') . '<br>';
    $db = $app->make(\Core\Database\DatabaseManager::class);
    $result = $db->raw('SELECT DATABASE() as db');
    echo 'Current DB: ' . ($result[0]->db ?? $result[0]['db'] ?? 'NULL');
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