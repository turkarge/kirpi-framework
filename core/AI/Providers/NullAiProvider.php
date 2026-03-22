<?php

declare(strict_types=1);

namespace Core\AI\Providers;

use Core\AI\Contracts\AiProviderInterface;

class NullAiProvider implements AiProviderInterface
{
    public function complete(string $prompt, array $options = []): array
    {
        return [
            'provider' => 'null',
            'message' => 'AI provider is not configured yet.',
            'prompt_preview' => mb_substr($prompt, 0, 160),
            'options' => $options,
            'timestamp' => date('Y-m-d H:i:s'),
        ];
    }
}
