<?php

declare(strict_types=1);

namespace Tests\Unit;

use Core\AI\Trace\AiTraceLogger;
use Core\AI\Trace\TraceMasker;
use Core\Logging\Logger;
use PHPUnit\Framework\TestCase;

class AiTraceLoggerTest extends TestCase
{
    public function test_it_writes_masked_trace_when_enabled(): void
    {
        $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'kirpi-ai-trace-test-' . bin2hex(random_bytes(4));
        mkdir($dir, 0755, true);

        $logger = new Logger($dir);
        $trace = new AiTraceLogger($logger, new TraceMasker(), true);
        $trace->info('ai.sql.success', [
            'token' => 'top-secret',
            'question' => 'user@example.com',
        ]);

        $logFile = $dir . DIRECTORY_SEPARATOR . date('Y-m-d') . '-ai-trace.log';
        $this->assertFileExists($logFile);
        $content = (string) file_get_contents($logFile);
        $this->assertStringContainsString('[REDACTED]', $content);
        $this->assertStringContainsString('[REDACTED_EMAIL]', $content);
    }
}
