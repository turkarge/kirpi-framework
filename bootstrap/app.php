<?php

declare(strict_types=1);

use Core\Container\Container;

$app = new Container();

// Temel path binding'leri
$app->instance('path.base',    BASE_PATH);
$app->instance('path.config',  BASE_PATH . '/config');
$app->instance('path.storage', BASE_PATH . '/storage');
$app->instance('path.public',  BASE_PATH . '/public');
$app->instance('path.modules', BASE_PATH . '/modules');

return $app;