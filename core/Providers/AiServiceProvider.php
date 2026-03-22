<?php

declare(strict_types=1);

namespace Core\Providers;

use Core\AI\AiManager;
use Core\AI\Contracts\AiProviderInterface;
use Core\AI\Providers\NullAiProvider;
use Core\Support\ServiceProvider;

class AiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $config = $this->app->make('config')->load('ai');

        $provider = $this->resolveProvider((string) ($config['default'] ?? 'null'));

        $this->app->instance(AiProviderInterface::class, $provider);
        $this->app->instance(AiManager::class, new AiManager($provider, $config));
        $this->app->instance('ai', $this->app->make(AiManager::class));
    }

    private function resolveProvider(string $driver): AiProviderInterface
    {
        return match ($driver) {
            'null' => new NullAiProvider(),
            default => new NullAiProvider(),
        };
    }
}
