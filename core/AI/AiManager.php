<?php

declare(strict_types=1);

namespace Core\AI;

use Core\AI\Contracts\AiProviderInterface;
use RuntimeException;

class AiManager
{
    /** @param array<string, mixed> $config */
    public function __construct(
        private readonly AiProviderInterface $provider,
        private readonly array $config = [],
    ) {}

    public function enabled(): bool
    {
        return (bool) ($this->config['enabled'] ?? false);
    }

    /**
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    public function complete(string $prompt, array $options = []): array
    {
        if (!$this->enabled()) {
            throw new RuntimeException('AI feature is disabled. Enable KIRPI_FEATURE_AI to use ai().');
        }

        if (!isset($options['model']) && isset($this->config['model'])) {
            $options['model'] = (string) $this->config['model'];
        }

        return $this->provider->complete($prompt, $options);
    }
}
