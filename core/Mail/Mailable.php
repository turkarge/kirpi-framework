<?php

declare(strict_types=1);

namespace Core\Mail;

abstract class Mailable
{
    protected string  $subject    = '';
    protected string  $from       = '';
    protected string  $fromName   = '';
    protected array   $to         = [];
    protected array   $cc         = [];
    protected array   $bcc        = [];
    protected array   $replyTo    = [];
    protected array   $attachments = [];
    protected ?string $htmlBody   = null;
    protected ?string $textBody   = null;

    abstract public function build(): static;

    // ─── Fluent API ──────────────────────────────────────────

    public function subject(string $subject): static
    {
        $this->subject = $subject;
        return $this;
    }

    public function from(string $email, string $name = ''): static
    {
        $this->from     = $email;
        $this->fromName = $name;
        return $this;
    }

    public function to(string|array $email, string $name = ''): static
    {
        if (is_array($email)) {
            foreach ($email as $e => $n) {
                $this->to[] = is_string($e)
                    ? ['email' => $e, 'name' => $n]
                    : ['email' => $n, 'name' => ''];
            }
        } else {
            $this->to[] = ['email' => $email, 'name' => $name];
        }
        return $this;
    }

    public function cc(string $email, string $name = ''): static
    {
        $this->cc[] = ['email' => $email, 'name' => $name];
        return $this;
    }

    public function bcc(string $email, string $name = ''): static
    {
        $this->bcc[] = ['email' => $email, 'name' => $name];
        return $this;
    }

    public function replyTo(string $email, string $name = ''): static
    {
        $this->replyTo[] = ['email' => $email, 'name' => $name];
        return $this;
    }

    public function html(string $content): static
    {
        $this->htmlBody = $content;
        return $this;
    }

    public function text(string $content): static
    {
        $this->textBody = $content;
        return $this;
    }

    public function attach(string $path, string $name = '', string $mime = ''): static
    {
        $this->attachments[] = [
            'path' => $path,
            'name' => $name ?: basename($path),
            'mime' => $mime ?: mime_content_type($path) ?: 'application/octet-stream',
        ];
        return $this;
    }

    // ─── Getters ─────────────────────────────────────────────

    public function getSubject(): string    { return $this->subject; }
    public function getFrom(): string       { return $this->from ?: env('MAIL_FROM_ADDRESS', ''); }
    public function getFromName(): string   { return $this->fromName ?: env('MAIL_FROM_NAME', ''); }
    public function getTo(): array          { return $this->to; }
    public function getCc(): array          { return $this->cc; }
    public function getBcc(): array         { return $this->bcc; }
    public function getReplyTo(): array     { return $this->replyTo; }
    public function getAttachments(): array { return $this->attachments; }
    public function getHtmlBody(): ?string  { return $this->htmlBody; }
    public function getTextBody(): ?string  { return $this->textBody; }
}