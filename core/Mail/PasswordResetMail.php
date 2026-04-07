<?php

declare(strict_types=1);

namespace Core\Mail;

class PasswordResetMail extends Mailable
{
    public function __construct(
        private readonly string $appName,
        private readonly string $resetUrl,
        private readonly int $expiresMinutes = 30,
    ) {}

    public function build(): static
    {
        $subject = __('mail.password_reset.subject', ['app' => $this->appName]);
        $greeting = __('mail.password_reset.greeting');
        $intro = __('mail.password_reset.intro', ['app' => $this->appName]);
        $action = __('mail.password_reset.action');
        $expiry = __('mail.password_reset.expiry', ['minutes' => (string) $this->expiresMinutes]);
        $ignore = __('mail.password_reset.ignore');

        return $this
            ->subject($subject)
            ->text(
                $greeting . "\n\n"
                . $intro . "\n\n"
                . $action . ": " . $this->resetUrl . "\n"
                . $expiry . "\n\n"
                . $ignore
            )
            ->html(
                '<h2>' . htmlspecialchars($greeting, ENT_QUOTES, 'UTF-8') . '</h2>'
                . '<p>' . htmlspecialchars($intro, ENT_QUOTES, 'UTF-8') . '</p>'
                . '<p><a href="' . htmlspecialchars($this->resetUrl, ENT_QUOTES, 'UTF-8') . '">'
                . htmlspecialchars($action, ENT_QUOTES, 'UTF-8')
                . '</a></p>'
                . '<p>' . htmlspecialchars($expiry, ENT_QUOTES, 'UTF-8') . '</p>'
                . '<p>' . htmlspecialchars($ignore, ENT_QUOTES, 'UTF-8') . '</p>'
            );
    }
}

