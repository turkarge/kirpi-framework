<?php

declare(strict_types=1);

namespace Core\Logging;

use Core\Support\RequestContext;

class Logger
{
    private string $path;
    private string $channel;
    private string $defaultFormat;
    private string $minLevel;
    /** @var array<int,string> */
    private array $redactKeys;
    /** @var array<string,mixed> */
    private array $channels;

    /** @var array<string,int> */
    private const LEVEL_PRIORITY = [
        'DEBUG' => 100,
        'INFO' => 200,
        'NOTICE' => 250,
        'WARNING' => 300,
        'ERROR' => 400,
        'CRITICAL' => 500,
        'ALERT' => 550,
        'EMERGENCY' => 600,
    ];

    /**
     * @param array<string,mixed> $config
     */
    public function __construct(
        string $path = '',
        string $channel = 'app',
        array $config = [],
    ) {
        $this->path = $path !== '' ? $path : (string) ($config['path'] ?? storage_path('logs'));
        $this->channel = $channel;
        $this->defaultFormat = strtolower((string) ($config['format'] ?? 'json'));
        $this->minLevel = strtoupper((string) ($config['level'] ?? 'DEBUG'));
        $this->redactKeys = array_map('strtolower', $config['redact_keys'] ?? [
            'password',
            'password_confirmation',
            'pin',
            'pin_confirmation',
            'token',
            'access_token',
            'refresh_token',
            'api_key',
            'secret',
            'authorization',
            'cookie',
            'set-cookie',
            'jwt',
            'app_key',
            'mail_password',
        ]);
        $this->channels = is_array($config['channels'] ?? null) ? $config['channels'] : [];
    }

    public function emergency(string $message, array $context = []): void
    {
        $this->log('EMERGENCY', $message, $context);
    }

    public function alert(string $message, array $context = []): void
    {
        $this->log('ALERT', $message, $context);
    }

    public function critical(string $message, array $context = []): void
    {
        $this->log('CRITICAL', $message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->log('ERROR', $message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->log('WARNING', $message, $context);
    }

    public function notice(string $message, array $context = []): void
    {
        $this->log('NOTICE', $message, $context);
    }

    public function info(string $message, array $context = []): void
    {
        $this->log('INFO', $message, $context);
    }

    public function debug(string $message, array $context = []): void
    {
        $this->log('DEBUG', $message, $context);
    }

    private function log(string $level, string $message, array $context = []): void
    {
        if (!$this->shouldLog($level)) {
            return;
        }

        $safeContext = $this->maskContext($context);
        $requestId = RequestContext::requestId();
        if ($requestId !== null && !isset($safeContext['request_id'])) {
            $safeContext['request_id'] = $requestId;
        }

        $record = [
            'timestamp' => date('Y-m-d H:i:s'),
            'level' => $level,
            'channel' => $this->channel,
            'message' => $message,
            'context' => $safeContext,
        ];

        $line = $this->formatLine($record) . PHP_EOL;
        $file = $this->path . DIRECTORY_SEPARATOR . date('Y-m-d') . '-' . $this->channel . '.log';

        if (!is_dir($this->path)) {
            mkdir($this->path, 0755, true);
        }

        file_put_contents($file, $line, FILE_APPEND | LOCK_EX);
    }

    /**
     * @param array<string,mixed> $record
     */
    private function formatLine(array $record): string
    {
        $format = $this->channelFormat();

        if ($format === 'line') {
            $context = $record['context'];
            $contextText = empty($context) ? '' : ' ' . (string) json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return '[' . $record['timestamp'] . '] ' . $record['channel'] . '.' . $record['level'] . ': ' . $record['message'] . $contextText;
        }

        return (string) json_encode($record, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function channelFormat(): string
    {
        $channelConfig = $this->channels[$this->channel] ?? null;
        if (is_array($channelConfig) && isset($channelConfig['format'])) {
            return strtolower((string) $channelConfig['format']);
        }

        return $this->defaultFormat;
    }

    private function shouldLog(string $level): bool
    {
        $current = self::LEVEL_PRIORITY[$level] ?? self::LEVEL_PRIORITY['DEBUG'];
        $minimum = self::LEVEL_PRIORITY[$this->minLevel] ?? self::LEVEL_PRIORITY['DEBUG'];
        return $current >= $minimum;
    }

    /**
     * @param array<string,mixed> $context
     * @return array<string,mixed>
     */
    private function maskContext(array $context): array
    {
        $masked = [];
        foreach ($context as $key => $value) {
            $masked[$key] = $this->maskValue((string) $key, $value);
        }

        return $masked;
    }

    private function maskValue(string $key, mixed $value): mixed
    {
        $lowerKey = strtolower($key);
        foreach ($this->redactKeys as $sensitiveKey) {
            if (str_contains($lowerKey, $sensitiveKey)) {
                return '[REDACTED]';
            }
        }

        if (is_array($value)) {
            $result = [];
            foreach ($value as $childKey => $childValue) {
                $result[$childKey] = $this->maskValue((string) $childKey, $childValue);
            }
            return $result;
        }

        if (is_object($value)) {
            if ($value instanceof \Stringable) {
                return (string) $value;
            }
            return '[OBJECT:' . $value::class . ']';
        }

        return $value;
    }

    public function channel(string $channel): static
    {
        $clone = clone $this;
        $clone->channel = $channel;
        return $clone;
    }
}

