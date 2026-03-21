<?php

declare(strict_types=1);

namespace Tests\Unit;

use Core\Cache\CacheManager;
use Core\Container\Container;
use Core\Database\DatabaseManager;
use Core\Runtime\RuntimeDiagnostics;
use PHPUnit\Framework\TestCase;

class RuntimeDiagnosticsTest extends TestCase
{
    public function test_runtime_diagnostics_service_is_resolvable_and_returns_payload_shapes(): void
    {
        $service = Container::getInstance()->make(RuntimeDiagnostics::class);

        $this->assertInstanceOf(RuntimeDiagnostics::class, $service);

        $checks = $service->checks();
        $this->assertArrayHasKey('database', $checks);
        $this->assertArrayHasKey('cache', $checks);

        $ready = $service->readinessPayload();
        $this->assertArrayHasKey('status', $ready);
        $this->assertArrayHasKey('checks', $ready);
        $this->assertArrayHasKey('policy', $ready);
        $this->assertArrayHasKey('reasons', $ready);

        $history = $service->historyPayload();
        $this->assertArrayHasKey('items', $history);
        $this->assertArrayHasKey('latency_trend', $history);
    }

    public function test_readiness_is_degraded_when_latency_threshold_is_exceeded(): void
    {
        $_ENV['KIRPI_READY_MAX_DB_LATENCY_MS'] = '1';
        $_SERVER['KIRPI_READY_MAX_DB_LATENCY_MS'] = '1';
        putenv('KIRPI_READY_MAX_DB_LATENCY_MS=1');

        $service = new class(
            $this->createMock(DatabaseManager::class),
            $this->createMock(CacheManager::class)
        ) extends RuntimeDiagnostics {
            public function checks(): array
            {
                return [
                    'database' => ['status' => 'up', 'message' => 'ok', 'latency_ms' => 10.0],
                    'cache' => ['status' => 'up', 'message' => 'ok', 'latency_ms' => 1.0],
                ];
            }
        };

        $ready = $service->readinessPayload();

        $this->assertSame('degraded', $ready['status']);
        $this->assertContains('database_latency_exceeded', $ready['reasons']);
    }

    protected function tearDown(): void
    {
        unset($_ENV['KIRPI_READY_MAX_DB_LATENCY_MS'], $_SERVER['KIRPI_READY_MAX_DB_LATENCY_MS']);
        putenv('KIRPI_READY_MAX_DB_LATENCY_MS');

        parent::tearDown();
    }
}
