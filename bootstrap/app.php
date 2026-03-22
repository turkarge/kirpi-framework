<?php

declare(strict_types=1);

use Core\Config\EnvLoader;
use Core\Container\Container;
use Core\Support\ServiceProvider;

EnvLoader::load(BASE_PATH);

$app = Container::getInstance();
$app->instance(Container::class, $app);

$app->instance('path.base', BASE_PATH);
$app->instance('path.config', base_path('config'));
$app->instance('path.storage', base_path('storage'));
$app->instance('path.public', base_path('public'));
$app->instance('path.modules', base_path('modules'));

$appConfig = [];
$appConfigPath = base_path('config/app.php');

if (file_exists($appConfigPath)) {
    $loaded = require $appConfigPath;
    $appConfig = is_array($loaded) ? $loaded : [];
}

$providers = (array) ($appConfig['providers'] ?? []);

if ($providers === []) {
    $providers = array_values(array_filter([
        Core\Providers\FoundationServiceProvider::class,
        Core\Providers\DatabaseServiceProvider::class,
        Core\Providers\AuthServiceProvider::class,
        Core\Providers\SupportServiceProvider::class,
        env('KIRPI_FEATURE_COMMUNICATION', true) ? Core\Providers\CommunicationServiceProvider::class : null,
        env('KIRPI_FEATURE_MONITORING', true) ? Core\Providers\MonitoringServiceProvider::class : null,
        env('KIRPI_FEATURE_AI', false) ? Core\Providers\AiServiceProvider::class : null,
        Core\Providers\RoutingServiceProvider::class,
    ], static fn(mixed $provider): bool => is_string($provider)));
}

$instances = array_map(
    fn(string $provider) => new $provider($app),
    $providers
);

array_walk($instances, fn(ServiceProvider $provider) => $provider->register());
array_walk($instances, fn(ServiceProvider $provider) => $provider->boot());

return $app;
