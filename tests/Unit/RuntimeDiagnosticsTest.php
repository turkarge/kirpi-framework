<?php

declare(strict_types=1);

namespace Tests\Unit;

use Core\Container\Container;
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

        $history = $service->historyPayload();
        $this->assertArrayHasKey('items', $history);
        $this->assertArrayHasKey('latency_trend', $history);
    }
}
