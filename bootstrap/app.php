<?php

declare(strict_types=1);

use Core\Container\Container;
use Core\Config\EnvLoader;
use Core\Config\Repository;

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

// Config repository
$config = new Repository(BASE_PATH . '/config');
$app->instance('config', $config);
$app->instance(Repository::class, $config);

return $app;