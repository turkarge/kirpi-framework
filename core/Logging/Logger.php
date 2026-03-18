<?php

declare(strict_types=1);

namespace Core\Logging;

class Logger
{
    private string $path;
    private string $channel;

    public function __construct(
        string $path    = '',
        string $channel = 'app',
    ) {
        $this->path    = $path ?: storage_path('logs');
        $this->channel = $channel;
    }

    // ─── PSR-3 Level'ları ────────────────────────────────────

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

    // ─── Core ────────────────────────────────────────────────

    private function log(string $level, string $message, array $context = []): void
    {
        $date    = date('Y-m-d H:i:s');
        $context = empty($context) ? '' : ' ' . json_encode($context, JSON_UNESCAPED_UNICODE);
        $line    = "[{$date}] {$this->channel}.{$level}: {$message}{$context}" . PHP_EOL;

        $file = $this->path . DIRECTORY_SEPARATOR . date('Y-m-d') . '-' . $this->channel . '.log';

        // Klasör yoksa oluştur
        if (!is_dir($this->path)) {
            mkdir($this->path, 0755, true);
        }

        file_put_contents($file, $line, FILE_APPEND | LOCK_EX);
    }

    public function channel(string $channel): static
    {
        $clone          = clone $this;
        $clone->channel = $channel;
        return $clone;
    }
}