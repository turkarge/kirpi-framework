<?php

declare(strict_types=1);

namespace Core\AI\Trace;

class TraceMasker
{
    /**
     * @param array<string, mixed> $context
     * @return array<string, mixed>
     */
    public function maskContext(array $context): array
    {
        return $this->maskValue($context);
    }

    private function maskValue(mixed $value): mixed
    {
        if (is_array($value)) {
            $masked = [];
            foreach ($value as $key => $item) {
                $masked[$key] = $this->maskByKey((string) $key, $item);
            }

            return $masked;
        }

        if (is_object($value)) {
            return $this->maskValue(get_object_vars($value));
        }

        if (!is_string($value)) {
            return $value;
        }

        return $this->maskString($value);
    }

    private function maskByKey(string $key, mixed $value): mixed
    {
        $lower = strtolower($key);
        $sensitiveKeys = ['password', 'token', 'secret', 'api_key', 'authorization', 'cookie'];
        foreach ($sensitiveKeys as $sensitive) {
            if (str_contains($lower, $sensitive)) {
                return '[REDACTED]';
            }
        }

        return $this->maskValue($value);
    }

    private function maskString(string $input): string
    {
        $output = $input;

        $output = (string) preg_replace('/Bearer\s+[A-Za-z0-9\-._~+\/]+=*/i', 'Bearer [REDACTED]', $output);
        $output = (string) preg_replace('/([A-Z0-9._%+\-]+)@([A-Z0-9.\-]+\.[A-Z]{2,})/i', '[REDACTED_EMAIL]', $output);
        $output = (string) preg_replace('/sk-[A-Za-z0-9]{16,}/', '[REDACTED_KEY]', $output);
        $output = (string) preg_replace('/(password["\']?\s*[:=]\s*["\']?)([^"\',\s]+)/i', '$1[REDACTED]', $output);

        return $output;
    }
}
