<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class AppConfigTest extends TestCase
{
    public function test_providers_include_optional_modules_by_default(): void
    {
        $this->setFeatureEnv('KIRPI_FEATURE_COMMUNICATION', null);
        $this->setFeatureEnv('KIRPI_FEATURE_MONITORING', null);

        $config = require BASE_PATH . '/config/app.php';
        $providers = $config['providers'] ?? [];

        $this->assertContains(\Core\Providers\CommunicationServiceProvider::class, $providers);
        $this->assertContains(\Core\Providers\MonitoringServiceProvider::class, $providers);
    }

    public function test_providers_exclude_optional_modules_when_feature_flags_are_disabled(): void
    {
        $this->setFeatureEnv('KIRPI_FEATURE_COMMUNICATION', 'false');
        $this->setFeatureEnv('KIRPI_FEATURE_MONITORING', 'false');

        $config = require BASE_PATH . '/config/app.php';
        $providers = $config['providers'] ?? [];

        $this->assertNotContains(\Core\Providers\CommunicationServiceProvider::class, $providers);
        $this->assertNotContains(\Core\Providers\MonitoringServiceProvider::class, $providers);
    }

    protected function tearDown(): void
    {
        $this->setFeatureEnv('KIRPI_FEATURE_COMMUNICATION', null);
        $this->setFeatureEnv('KIRPI_FEATURE_MONITORING', null);

        parent::tearDown();
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