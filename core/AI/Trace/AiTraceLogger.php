<?php

declare(strict_types=1);

namespace Core\AI\Trace;

use Core\Logging\Logger;

class AiTraceLogger
{
    public function __construct(
        private readonly Logger $logger,
        private readonly TraceMasker $masker,
        private readonly bool $enabled = false,
    ) {}

    public function enabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function info(string $message, array $context = []): void
    {
        if (!$this->enabled) {
            return;
        }

        $this->logger->channel('ai-trace')->info($message, $this->masker->maskContext($context));
    }

    /**
     * @param array<string, mixed> $context
     */
    public function error(string $message, array $context = []): void
    {
        if (!$this->enabled) {
            return;
        }

        $this->logger->channel('ai-trace')->error($message, $this->masker->maskContext($context));
    }
}
