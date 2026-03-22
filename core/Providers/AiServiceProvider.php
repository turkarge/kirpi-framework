<?php

declare(strict_types=1);

namespace Core\Providers;

use Core\AI\AiManager;
use Core\AI\Sql\SchemaInspector;
use Core\AI\Sql\SqlAgent;
use Core\AI\Sql\SqlGuard;
use Core\AI\Trace\AiTraceLogger;
use Core\AI\Trace\TraceMasker;
use Core\AI\Contracts\AiProviderInterface;
use Core\AI\Providers\NullAiProvider;
use Core\AI\Providers\OllamaProvider;
use Core\Database\DatabaseManager;
use Core\Logging\Logger;
use Core\Support\ServiceProvider;

class AiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $config = $this->app->make('config')->load('ai');

        $provider = $this->resolveProvider(
            driver: (string) ($config['default'] ?? 'null'),
            providers: (array) ($config['providers'] ?? [])
        );

        $this->app->instance(AiProviderInterface::class, $provider);
        $this->app->instance(AiManager::class, new AiManager($provider, $config));
        $this->app->instance('ai', $this->app->make(AiManager::class));

        $this->app->singleton(TraceMasker::class, fn () => new TraceMasker());
        $this->app->singleton(AiTraceLogger::class, fn () => new AiTraceLogger(
            logger: $this->app->make(Logger::class),
            masker: $this->app->make(TraceMasker::class),
            enabled: (bool) (($config['trace']['enabled'] ?? false)),
        ));
        $this->app->singleton(SqlGuard::class, fn () => new SqlGuard((array) ($config['sql'] ?? [])));
        $this->app->singleton(SchemaInspector::class, fn () => new SchemaInspector($this->app->make(DatabaseManager::class)));
        $this->app->singleton(SqlAgent::class, fn () => new SqlAgent(
            $this->app->make(AiManager::class),
            $this->app->make(DatabaseManager::class),
            $this->app->make(SchemaInspector::class),
            $this->app->make(SqlGuard::class),
            $this->app->make(AiTraceLogger::class),
        ));
    }

    /** @param array<string, mixed> $providers */
    private function resolveProvider(string $driver, array $providers): AiProviderInterface
    {
        return match ($driver) {
            'ollama' => new OllamaProvider((array) ($providers['ollama'] ?? [])),
            'null' => new NullAiProvider(),
            default => new NullAiProvider(),
        };
    }
}
