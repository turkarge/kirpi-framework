<?php

declare(strict_types=1);

define('KIRPI_START', microtime(true));
define('BASE_PATH', dirname(__DIR__));

require BASE_PATH . '/vendor/autoload.php';

// Test env
$_ENV['APP_ENV']        = 'testing';
$_ENV['APP_DEBUG']      = 'true';
$_ENV['APP_KEY']        = 'kirpi-test-key-32-characters-long';
$_ENV['DB_CONNECTION']  = 'sqlite';
$_ENV['SQLITE_DATABASE']= ':memory:';
$_ENV['CACHE_DRIVER']   = 'array';
$_ENV['QUEUE_DRIVER']   = 'sync';
$_ENV['MAIL_DRIVER']    = 'log';

// Bootstrap
$app = require BASE_PATH . '/bootstrap/app.php';

// Test için global app instance
\Core\Container\Container::setInstance($app);