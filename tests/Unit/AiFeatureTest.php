<?php

declare(strict_types=1);

namespace Tests\Unit;

use Core\AI\AiManager;
use Core\Container\Container;
use Core\Providers\AiServiceProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class AiFeatureTest extends TestCase
{
    protected function tearDown(): void
    {
        $this->setFeatureEnv('KIRPI_FEATURE_AI', null);
        $this->setFeatureEnv('AI_PROVIDER', null);

        parent::tearDown();
    }

    public function test_ai_provider_can_be_registered_and_returns_stub_payload(): void
    {
        $this->setFeatureEnv('KIRPI_FEATURE_AI', 'true');
        $this->setFeatureEnv('AI_PROVIDER', 'null');

        $app = Container::getInstance();
        $provider = new AiServiceProvider($app);
        $provider->register();

        $result = $app->make(AiManager::class)->complete('Merhaba Kirpi');

        $this->assertSame('null', $result['provider']);
        $this->assertArrayHasKey('message', $result);
    }

    public function test_ai_manager_throws_when_feature_disabled(): void
    {
        $this->setFeatureEnv('KIRPI_FEATURE_AI', 'false');

        $manager = new AiManager(new \Core\AI\Providers\NullAiProvider(), ['enabled' => false]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('AI feature is disabled');

        $manager->complete('test');
    }

    private function setFeatureEnv(string $key, ?string $value): void
    {
        if ($value === null) {
            unset($_ENV[$key], $_SERVER[$key]);
            putenv($key);
            return;
        }

        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
        putenv($key . '=' . $value);
    }
}
