<?php

declare(strict_types=1);

define('KIRPI_START', microtime(true));
define('BASE_PATH', dirname(__DIR__));

// Autoloader
require BASE_PATH . '/vendor/autoload.php';

// Bootstrap
$app = require BASE_PATH . '/bootstrap/app.php';

// HTTP Kernel
$kernel = $app->make(\Core\Http\Kernel::class);

$request  = \Core\Http\Request::capture();
$response = $kernel->handle($request);

$response->send();

$kernel->terminate($request, $response);