<?php

declare(strict_types=1);

namespace Core\Providers;

use Core\Config\Repository;
use Core\Exception\Handler;
use Core\Logging\Logger;
use Core\Support\ServiceProvider;

class FoundationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $config = new Repository(base_path('config'));
        $this->app->instance('config', $config);
        $this->app->instance(Repository::class, $config);

        $loggingConfig = $config->load('logging');
        $logger = new Logger(
            path: (string) ($loggingConfig['path'] ?? storage_path('logs')),
            channel: 'app',
            config: is_array($loggingConfig) ? $loggingConfig : []
        );
        $this->app->instance('logger', $logger);
        $this->app->instance(Logger::class, $logger);

        $handler = new Handler(
            logger: $logger,
            debug: (bool) env('APP_DEBUG', false),
        );
        $this->app->instance(Handler::class, $handler);
    }

    public function boot(): void
    {
        $this->app->make(Handler::class)->register();
    }
}
