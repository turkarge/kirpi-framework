<?php

declare(strict_types=1);

namespace Core\Providers;

use Core\Cache\CacheManager;
use Core\Database\DatabaseManager;
use Core\Monitor\HealthChecker;
use Core\Monitor\MetricsCollector;
use Core\Monitor\MonitorController;
use Core\Support\ServiceProvider;

class MonitoringServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $db = $this->app->make(DatabaseManager::class);
        $cache = $this->app->make(CacheManager::class);

        $this->app->singleton(HealthChecker::class, fn() => new HealthChecker($db, $cache));
        $this->app->singleton(MetricsCollector::class, fn() => new MetricsCollector($db));
        $this->app->singleton(MonitorController::class, fn() => new MonitorController(
            app(HealthChecker::class),
            app(MetricsCollector::class),
        ));
    }
}