<?php

declare(strict_types=1);

namespace Tests\Unit;

use Core\AI\Trace\TraceMasker;
use PHPUnit\Framework\TestCase;

class AiTraceMaskerTest extends TestCase
{
    public function test_it_masks_sensitive_keys_and_email_and_bearer_tokens(): void
    {
        $masker = new TraceMasker();

        $result = $masker->maskContext([
            'token' => 'secret-token-value',
            'payload' => [
                'email' => 'user@example.com',
                'note' => 'Bearer abcdefghijklmnop',
            ],
        ]);

        $this->assertSame('[REDACTED]', $result['token']);
        $this->assertSame('[REDACTED_EMAIL]', $result['payload']['email']);
        $this->assertStringContainsString('Bearer [REDACTED]', $result['payload']['note']);
    }
}
