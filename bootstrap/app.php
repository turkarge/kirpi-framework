<?php

declare(strict_types=1);

use Core\Container\Container;
use Core\Config\EnvLoader;
use Core\Config\Repository;
use Core\Logging\Logger;
use Core\Exception\Handler;

// Env yükle
EnvLoader::load(BASE_PATH);

// Container
$app = Container::getInstance();

// Path binding'leri
$app->instance('path.base',    BASE_PATH);
$app->instance('path.config',  BASE_PATH . '/config');
$app->instance('path.storage', BASE_PATH . '/storage');
$app->instance('path.public',  BASE_PATH . '/public');
$app->instance('path.modules', BASE_PATH . '/modules');

// Config
$config = new Repository(BASE_PATH . '/config');
$app->instance('config', $config);
$app->instance(Repository::class, $config);

// Logger
$logger = new Logger(storage_path('logs'));
$app->instance('logger', $logger);
$app->instance(Logger::class, $logger);

// Exception Handler
$handler = new Handler(
    logger: $logger,
    debug:  (bool) env('APP_DEBUG', false),
);
$app->instance(Handler::class, $handler);
$handler->register();

return $app;