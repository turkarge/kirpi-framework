<?php

declare(strict_types=1);

namespace Core\AI\Providers;

use Core\AI\Contracts\AiProviderInterface;
use Core\Http\Client\HttpClient;
use RuntimeException;

class AnthropicProvider implements AiProviderInterface
{
    /** @param array<string, mixed> $config */
    public function __construct(
        private readonly array $config = [],
    ) {}

    public function complete(string $prompt, array $options = []): array
    {
        $apiKey = trim((string) ($this->config['api_key'] ?? ''));
        if ($apiKey === '') {
            throw new RuntimeException('Anthropic API key is missing. Set AI_ANTHROPIC_API_KEY.');
        }

        $baseUrl = (string) ($this->config['base_url'] ?? 'https://api.anthropic.com/v1');
        $model = (string) ($options['model'] ?? $this->config['model'] ?? 'claude-3-5-haiku-latest');
        $timeout = (int) ($options['timeout'] ?? $this->config['timeout'] ?? 60);
        $system = trim((string) ($options['system'] ?? ''));
        $maxTokens = (int) (($options['generation']['max_tokens'] ?? 800));

        $payload = [
            'model' => $model,
            'max_tokens' => max(1, $maxTokens),
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
        ];

        if ($system !== '') {
            $payload['system'] = $system;
        }

        $generationOptions = $options['generation'] ?? null;
        if (is_array($generationOptions) && isset($generationOptions['temperature'])) {
            $payload['temperature'] = (float) $generationOptions['temperature'];
        }

        $response = HttpClient::new()
            ->baseUrl($baseUrl)
            ->acceptJson()
            ->asJson()
            ->withHeaders([
                'x-api-key' => $apiKey,
                'anthropic-version' => '2023-06-01',
            ])
            ->timeout($timeout)
            ->post('/messages', $payload);

        if (!$response->successful()) {
            $body = trim($response->body());
            throw new RuntimeException('Anthropic request failed with status ' . $response->status() . ($body !== '' ? ' body: ' . $body : ''));
        }

        $json = $response->json();
        if (!is_array($json)) {
            throw new RuntimeException('Anthropic returned invalid JSON payload.');
        }

        return [
            'provider' => 'anthropic',
            'model' => $model,
            'content' => $this->extractContent($json),
            'raw' => $json,
        ];
    }

    /**
     * @param array<string, mixed> $json
     */
    private function extractContent(array $json): string
    {
        $content = $json['content'] ?? null;
        if (!is_array($content)) {
            return '';
        }

        $parts = [];
        foreach ($content as $item) {
            if (is_array($item) && (($item['type'] ?? null) === 'text') && isset($item['text']) && is_string($item['text'])) {
                $parts[] = $item['text'];
            }
        }

        return trim(implode("\n", $parts));
    }
}

