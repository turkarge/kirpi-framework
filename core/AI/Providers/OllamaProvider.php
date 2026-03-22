<?php

declare(strict_types=1);

namespace Core\AI\Providers;

use Core\AI\Contracts\AiProviderInterface;
use Core\Http\Client\HttpClient;
use RuntimeException;

class OllamaProvider implements AiProviderInterface
{
    /** @param array<string, mixed> $config */
    public function __construct(
        private readonly array $config = [],
    ) {}

    public function complete(string $prompt, array $options = []): array
    {
        $baseUrl = (string) ($this->config['base_url'] ?? 'http://ollama:11434');
        $model = (string) ($options['model'] ?? $this->config['model'] ?? 'qwen2.5-coder:3b');
        $timeout = (int) ($options['timeout'] ?? $this->config['timeout'] ?? 60);
        $system = (string) ($options['system'] ?? '');

        $payload = [
            'model' => $model,
            'prompt' => $prompt,
            'system' => $system !== '' ? $system : null,
            'stream' => false,
        ];

        $generationOptions = $options['generation'] ?? null;
        if (is_array($generationOptions) && $generationOptions !== []) {
            $payload['options'] = $generationOptions;
        }

        $payload = array_filter($payload, static fn (mixed $value): bool => $value !== null);

        $response = HttpClient::new()
            ->baseUrl($baseUrl)
            ->acceptJson()
            ->asJson()
            ->timeout($timeout)
            ->post('/api/generate', $payload);

        if (!$response->successful()) {
            $status = $response->status();
            $body = trim($response->body());
            if ($status === 404) {
                throw new RuntimeException('Ollama model not found. Pull it first: ollama pull ' . $model);
            }

            throw new RuntimeException('Ollama request failed with status ' . $status . ($body !== '' ? ' body: ' . $body : ''));
        }

        $json = $response->json();
        if (!is_array($json)) {
            throw new RuntimeException('Ollama returned invalid JSON payload.');
        }

        return [
            'provider' => 'ollama',
            'model' => $model,
            'content' => (string) ($json['response'] ?? ''),
            'done' => (bool) ($json['done'] ?? true),
            'raw' => $json,
        ];
    }
}
