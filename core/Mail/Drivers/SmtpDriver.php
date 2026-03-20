<?php

declare(strict_types=1);

namespace Core\Mail\Drivers;

use Core\Mail\MailDriverInterface;
use Core\Mail\Mailable;

class SmtpDriver implements MailDriverInterface
{
    private string $host;
    private int    $port;
    private string $encryption;
    private string $username;
    private string $password;
    private int    $timeout;

    public function __construct(array $config)
    {
        $this->host       = $config['host']       ?? 'localhost';
        $this->port       = (int) ($config['port'] ?? 587);
        $this->encryption = $config['encryption'] ?? 'tls';
        $this->username   = $config['username']   ?? '';
        $this->password   = $config['password']   ?? '';
        $this->timeout    = (int) ($config['timeout'] ?? 30);
    }

    public function send(Mailable $mailable): bool
    {
        $mailable->build();

        $socket = $this->connect();

        if (!$socket) {
            throw new \RuntimeException("Could not connect to SMTP server: {$this->host}:{$this->port}");
        }

        try {
            $this->handshake($socket);
            $this->authenticate($socket);
            $this->sendMessage($socket, $mailable);
            $this->quit($socket);
            return true;
        } finally {
            fclose($socket);
        }
    }

    public function getDriverName(): string
    {
        return 'smtp';
    }

    // ─── SMTP Protocol ───────────────────────────────────────

    private function connect(): mixed
    {
        $prefix = match($this->encryption) {
            'ssl'  => 'ssl://',
            default => '',
        };

        $socket = fsockopen(
            "{$prefix}{$this->host}",
            $this->port,
            $errno,
            $errstr,
            $this->timeout
        );

        if (!$socket) {
            throw new \RuntimeException("SMTP connection failed: {$errstr} ({$errno})");
        }

        stream_set_timeout($socket, $this->timeout);

        // Greeting
        $this->read($socket);

        return $socket;
    }

    private function handshake(mixed $socket): void
    {
        $this->command($socket, "EHLO {$this->host}", 250);

        if ($this->encryption === 'tls') {
            $this->command($socket, 'STARTTLS', 220);

            stream_socket_enable_crypto(
                $socket,
                true,
                STREAM_CRYPTO_METHOD_TLS_CLIENT
            );

            $this->command($socket, "EHLO {$this->host}", 250);
        }
    }

    private function authenticate(mixed $socket): void
    {
        if (empty($this->username)) return;

        $this->command($socket, 'AUTH LOGIN', 334);
        $this->command($socket, base64_encode($this->username), 334);
        $this->command($socket, base64_encode($this->password), 235);
    }

    private function sendMessage(mixed $socket, Mailable $mailable): void
    {
        // MAIL FROM
        $this->command($socket, "MAIL FROM:<{$mailable->getFrom()}>", 250);

        // RCPT TO
        foreach ($mailable->getTo() as $recipient) {
            $this->command($socket, "RCPT TO:<{$recipient['email']}>", 250);
        }

        foreach ($mailable->getCc() as $cc) {
            $this->command($socket, "RCPT TO:<{$cc['email']}>", 250);
        }

        foreach ($mailable->getBcc() as $bcc) {
            $this->command($socket, "RCPT TO:<{$bcc['email']}>", 250);
        }

        // DATA
        $this->command($socket, 'DATA', 354);

        $message = $this->buildMessage($mailable);
        fwrite($socket, $message . "\r\n.\r\n");

        $this->read($socket, 250);
    }

    private function buildMessage(Mailable $mailable): string
    {
        $boundary = md5(uniqid((string) time()));
        $headers  = [];

        // Headers
        $fromName = $mailable->getFromName()
            ? "=?UTF-8?B?" . base64_encode($mailable->getFromName()) . "?= <{$mailable->getFrom()}>"
            : $mailable->getFrom();

        $headers[] = "From: {$fromName}";
        $headers[] = "To: " . $this->formatRecipients($mailable->getTo());
        $headers[] = "Subject: =?UTF-8?B?" . base64_encode($mailable->getSubject()) . "?=";
        $headers[] = "MIME-Version: 1.0";
        $headers[] = "Date: " . date('r');
        $headers[] = "Message-ID: <" . uniqid() . "@" . $this->host . ">";

        if (!empty($mailable->getCc())) {
            $headers[] = "Cc: " . $this->formatRecipients($mailable->getCc());
        }

        if (!empty($mailable->getReplyTo())) {
            $headers[] = "Reply-To: " . $this->formatRecipients($mailable->getReplyTo());
        }

        // Body
        $html = $mailable->getHtmlBody();
        $text = $mailable->getTextBody();

        if ($html && $text) {
            $headers[] = "Content-Type: multipart/alternative; boundary=\"{$boundary}\"";
            $body  = "--{$boundary}\r\n";
            $body .= "Content-Type: text/plain; charset=UTF-8\r\n\r\n";
            $body .= $text . "\r\n";
            $body .= "--{$boundary}\r\n";
            $body .= "Content-Type: text/html; charset=UTF-8\r\n\r\n";
            $body .= $html . "\r\n";
            $body .= "--{$boundary}--";
        } elseif ($html) {
            $headers[] = "Content-Type: text/html; charset=UTF-8";
            $headers[] = "Content-Transfer-Encoding: base64";
            $body = chunk_split(base64_encode($html));
        } else {
            $headers[] = "Content-Type: text/plain; charset=UTF-8";
            $body = $text ?? '';
        }

        return implode("\r\n", $headers) . "\r\n\r\n" . $body;
    }

    private function formatRecipients(array $recipients): string
    {
        return implode(', ', array_map(function ($r) {
            return $r['name']
                ? "=?UTF-8?B?" . base64_encode($r['name']) . "?= <{$r['email']}>"
                : $r['email'];
        }, $recipients));
    }

    private function quit(mixed $socket): void
    {
        $this->command($socket, 'QUIT', 221);
    }

    private function command(mixed $socket, string $command, int $expectedCode): string
    {
        fwrite($socket, $command . "\r\n");
        return $this->read($socket, $expectedCode);
    }

    private function read(mixed $socket, ?int $expectedCode = null): string
    {
        $response = '';

        while ($line = fgets($socket, 515)) {
            $response .= $line;
            if (substr($line, 3, 1) === ' ') break;
        }

        if ($expectedCode !== null) {
            $code = (int) substr($response, 0, 3);
            if ($code !== $expectedCode) {
                throw new \RuntimeException(
                    "SMTP error: Expected {$expectedCode}, got {$code}. Response: {$response}"
                );
            }
        }

        return $response;
    }
}