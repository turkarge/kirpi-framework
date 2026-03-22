<?php

declare(strict_types=1);

namespace Tests\Unit;

use Core\AI\Contracts\AiProviderInterface;
use Core\AI\Providers\AnthropicProvider;
use Core\AI\Providers\NullAiProvider;
use Core\AI\Providers\OpenAiProvider;
use Core\Container\Container;
use Core\Providers\AiServiceProvider;
use PHPUnit\Framework\TestCase;

class AiProviderResolutionTest extends TestCase
{
    public function test_it_resolves_openai_provider(): void
    {
        $provider = $this->resolve('openai', [
            'openai' => ['driver' => 'openai'],
        ]);

        $this->assertInstanceOf(OpenAiProvider::class, $provider);
    }

    public function test_it_resolves_anthropic_provider(): void
    {
        $provider = $this->resolve('anthropic', [
            'anthropic' => ['driver' => 'anthropic'],
        ]);

        $this->assertInstanceOf(AnthropicProvider::class, $provider);
    }

    public function test_it_falls_back_to_null_provider_for_unknown_driver(): void
    {
        $provider = $this->resolve('unknown', []);

        $this->assertInstanceOf(NullAiProvider::class, $provider);
    }

    /**
     * @param array<string, mixed> $providers
     */
    private function resolve(string $driver, array $providers): AiProviderInterface
    {
        $serviceProvider = new AiServiceProvider(new Container());
        $method = new \ReflectionMethod(AiServiceProvider::class, 'resolveProvider');
        $method->setAccessible(true);

        /** @var AiProviderInterface $provider */
        $provider = $method->invoke($serviceProvider, $driver, $providers);

        return $provider;
    }
}

