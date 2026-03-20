<?php

declare(strict_types=1);

namespace Core\Mail;

use Core\Mail\Drivers\SmtpDriver;
use Core\Mail\Drivers\LogDriver;
use Core\Mail\Drivers\MailgunDriver;

class MailManager
{
    private array $resolved = [];

    public function __construct(private readonly array $config) {}

    // ─── Driver ──────────────────────────────────────────────

    public function driver(?string $name = null): MailDriverInterface
    {
        $name ??= $this->config['default'] ?? 'log';

        if (isset($this->resolved[$name])) {
            return $this->resolved[$name];
        }

        $config = $this->config['drivers'][$name]
            ?? throw new \InvalidArgumentException("Mail driver [{$name}] not configured.");

        return $this->resolved[$name] = $this->createDriver($config);
    }

    private function createDriver(array $config): MailDriverInterface
    {
        return match($config['driver']) {
            'smtp'    => new SmtpDriver($config),
            'mailgun' => new MailgunDriver($config),
            'log'     => new LogDriver(),
            default   => throw new \RuntimeException("Mail driver [{$config['driver']}] not supported."),
        };
    }

    // ─── Fluent API ──────────────────────────────────────────

    public function to(string|array $email, string $name = ''): PendingMail
    {
        return (new PendingMail($this))->to($email, $name);
    }

    public function cc(string $email, string $name = ''): PendingMail
    {
        return (new PendingMail($this))->cc($email, $name);
    }

    public function bcc(string $email, string $name = ''): PendingMail
    {
        return (new PendingMail($this))->bcc($email, $name);
    }

    // ─── Send ────────────────────────────────────────────────

    public function send(Mailable $mailable): bool
    {
        return $this->driver()->send($mailable);
    }

    public function later(int $delay, Mailable $mailable): string
    {
        // Queue sistemi ile entegre — şimdilik direkt gönder
        return $this->send($mailable) ? 'sent' : 'failed';
    }
}