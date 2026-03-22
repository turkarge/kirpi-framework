<?php

declare(strict_types=1);

namespace Tests\Unit;

use Core\AI\AiManager;
use Core\AI\Providers\NullAiProvider;
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

    public function test_ai_manager_returns_stub_payload_with_null_provider(): void
    {
        $manager = new AiManager(new NullAiProvider(), ['enabled' => true, 'model' => 'null']);
        $result = $manager->complete('Merhaba Kirpi');

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

    public function test_sql_agent_summary_prefers_count_like_aliases(): void
    {
        $agent = new class extends \Core\AI\Sql\SqlAgent {
            public function __construct() {}
            public function callSummary(array $rows): string
            {
                $method = new \ReflectionMethod(\Core\AI\Sql\SqlAgent::class, 'summarizeRows');
                $method->setAccessible(true);
                return (string) $method->invoke($this, $rows);
            }
        };

        $summary = $agent->callSummary([['user_count' => 3]]);
        $this->assertSame('User count: 3', $summary);
    }

    public function test_sql_agent_summary_handles_raw_count_expression_key(): void
    {
        $agent = new class extends \Core\AI\Sql\SqlAgent {
            public function __construct() {}
            public function callSummary(array $rows): string
            {
                $method = new \ReflectionMethod(\Core\AI\Sql\SqlAgent::class, 'summarizeRows');
                $method->setAccessible(true);
                return (string) $method->invoke($this, $rows);
            }
        };

        $summary = $agent->callSummary([['COUNT(*)' => 3]]);
        $this->assertSame('COUNT(*): 3', $summary);
    }

    public function test_sql_agent_retry_policy_matches_guarded_keyword_errors(): void
    {
        $agent = new class extends \Core\AI\Sql\SqlAgent {
            public function __construct() {}
            public function canRetry(string $message): bool
            {
                $method = new \ReflectionMethod(\Core\AI\Sql\SqlAgent::class, 'shouldRetryAfterGuardError');
                $method->setAccessible(true);
                return (bool) $method->invoke($this, $message);
            }
        };

        $this->assertTrue($agent->canRetry('Blocked keyword detected in SQL: update'));
        $this->assertFalse($agent->canRetry('Table not found in known schema: x'));
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
