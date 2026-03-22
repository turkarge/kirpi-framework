<?php

declare(strict_types=1);

namespace Core\AI\Providers;

use Core\AI\Contracts\AiProviderInterface;
use Core\Http\Client\HttpClient;
use RuntimeException;

class OpenAiProvider implements AiProviderInterface
{
    /** @param array<string, mixed> $config */
    public function __construct(
        private readonly array $config = [],
    ) {}

    public function complete(string $prompt, array $options = []): array
    {
        $apiKey = trim((string) ($this->config['api_key'] ?? ''));
        if ($apiKey === '') {
            throw new RuntimeException('OpenAI API key is missing. Set AI_OPENAI_API_KEY.');
        }

        $baseUrl = (string) ($this->config['base_url'] ?? 'https://api.openai.com/v1');
        $model = (string) ($options['model'] ?? $this->config['model'] ?? 'gpt-4.1-mini');
        $timeout = (int) ($options['timeout'] ?? $this->config['timeout'] ?? 60);
        $system = trim((string) ($options['system'] ?? ''));

        $messages = [];
        if ($system !== '') {
            $messages[] = ['role' => 'system', 'content' => $system];
        }
        $messages[] = ['role' => 'user', 'content' => $prompt];

        $payload = [
            'model' => $model,
            'messages' => $messages,
        ];

        $generationOptions = $options['generation'] ?? null;
        if (is_array($generationOptions) && $generationOptions !== []) {
            if (isset($generationOptions['temperature'])) {
                $payload['temperature'] = (float) $generationOptions['temperature'];
            }
            if (isset($generationOptions['max_tokens'])) {
                $payload['max_tokens'] = (int) $generationOptions['max_tokens'];
            }
        }

        $response = HttpClient::new()
            ->baseUrl($baseUrl)
            ->acceptJson()
            ->asJson()
            ->withToken($apiKey)
            ->timeout($timeout)
            ->post('/chat/completions', $payload);

        if (!$response->successful()) {
            $body = trim($response->body());
            throw new RuntimeException('OpenAI request failed with status ' . $response->status() . ($body !== '' ? ' body: ' . $body : ''));
        }

        $json = $response->json();
        if (!is_array($json)) {
            throw new RuntimeException('OpenAI returned invalid JSON payload.');
        }

        return [
            'provider' => 'openai',
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
        $content = $json['choices'][0]['message']['content'] ?? '';

        if (is_string($content)) {
            return trim($content);
        }

        if (!is_array($content)) {
            return '';
        }

        $parts = [];
        foreach ($content as $item) {
            if (is_array($item) && isset($item['text']) && is_string($item['text'])) {
                $parts[] = $item['text'];
            }
        }

        return trim(implode("\n", $parts));
    }
}

