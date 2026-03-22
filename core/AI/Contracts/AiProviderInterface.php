<?php

declare(strict_types=1);

namespace Core\AI\Contracts;

interface AiProviderInterface
{
    /**
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    public function complete(string $prompt, array $options = []): array;
}
