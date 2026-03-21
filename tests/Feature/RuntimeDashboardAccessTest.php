<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\Support\TestCase;

class RuntimeDashboardAccessTest extends TestCase
{
    public function test_dashboard_is_hidden_in_production_by_default(): void
    {
        $this->setEnv('APP_ENV', 'production');
        $this->clearEnv('KIRPI_RUNTIME_DASHBOARD_IN_PRODUCTION');

        $response = $this->get('/kirpi');

        $this->assertResponseStatus($response, 404);
    }

    public function test_dashboard_can_be_enabled_in_production(): void
    {
        $this->setEnv('APP_ENV', 'production');
        $this->setEnv('KIRPI_RUNTIME_DASHBOARD_IN_PRODUCTION', 'true');

        $response = $this->get('/kirpi');

        $this->assertResponseStatus($response, 200);
        $this->assertStringContainsString('Kirpi Runtime', $response->getContent());
    }

    protected function tearDown(): void
    {
        $this->setEnv('APP_ENV', 'testing');
        $this->clearEnv('KIRPI_RUNTIME_DASHBOARD_IN_PRODUCTION');

        parent::tearDown();
    }

    private function setEnv(string $key, string $value): void
    {
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
        putenv($key . '=' . $value);
    }

    private function clearEnv(string $key): void
    {
        unset($_ENV[$key], $_SERVER[$key]);
        putenv($key);
    }
}
