<?php

declare(strict_types=1);

namespace Tests\Unit;

use Core\AI\Providers\AnthropicProvider;
use Core\AI\Providers\OpenAiProvider;
use PHPUnit\Framework\TestCase;

class AiProviderContentExtractionTest extends TestCase
{
    public function test_openai_provider_extracts_string_content(): void
    {
        $provider = new OpenAiProvider();
        $method = new \ReflectionMethod(OpenAiProvider::class, 'extractContent');
        $method->setAccessible(true);

        $content = $method->invoke($provider, [
            'choices' => [
                ['message' => ['content' => 'SELECT id FROM users LIMIT 10']],
            ],
        ]);

        $this->assertSame('SELECT id FROM users LIMIT 10', $content);
    }

    public function test_openai_provider_extracts_array_text_content(): void
    {
        $provider = new OpenAiProvider();
        $method = new \ReflectionMethod(OpenAiProvider::class, 'extractContent');
        $method->setAccessible(true);

        $content = $method->invoke($provider, [
            'choices' => [
                ['message' => ['content' => [
                    ['type' => 'output_text', 'text' => 'SELECT id'],
                    ['type' => 'output_text', 'text' => 'FROM users LIMIT 5'],
                ]]],
            ],
        ]);

        $this->assertSame("SELECT id\nFROM users LIMIT 5", $content);
    }

    public function test_anthropic_provider_extracts_text_blocks(): void
    {
        $provider = new AnthropicProvider();
        $method = new \ReflectionMethod(AnthropicProvider::class, 'extractContent');
        $method->setAccessible(true);

        $content = $method->invoke($provider, [
            'content' => [
                ['type' => 'text', 'text' => 'SELECT id, email'],
                ['type' => 'text', 'text' => 'FROM users LIMIT 10'],
            ],
        ]);

        $this->assertSame("SELECT id, email\nFROM users LIMIT 10", $content);
    }
}

