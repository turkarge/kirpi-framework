<?php

declare(strict_types=1);

define('KIRPI_START', microtime(true));
define('BASE_PATH', dirname(__DIR__));

require BASE_PATH . '/vendor/autoload.php';

$app = require BASE_PATH . '/bootstrap/app.php';

echo json_encode([
    'framework' => '🦔 Kirpi Framework',
    'version'   => '1.0.0',
    'php'       => PHP_VERSION,
    'env'       => env('APP_ENV', 'local'),
    'debug'     => env('APP_DEBUG', false),
    'status'    => 'running',
    'time'      => round((microtime(true) - KIRPI_START) * 1000, 2) . 'ms',
]);