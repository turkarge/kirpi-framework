<?php

declare(strict_types=1);

namespace Core\Mail;

class WelcomeMail extends Mailable
{
    public function __construct(
        private readonly string $name,
    ) {}

    public function build(): static
    {
        return $this
            ->subject("Kirpi Framework'e Hoş Geldiniz!")
            ->from(env('MAIL_FROM_ADDRESS', 'noreply@kirpi.dev'), env('MAIL_FROM_NAME', 'Kirpi'))
            ->html($this->buildHtml())
            ->text("Merhaba {$this->name}, Kirpi Framework'e hoş geldiniz!");
    }

    private function buildHtml(): string
    {
        return <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; background: #f5f5f5; padding: 20px; }
                .container { background: white; padding: 40px; border-radius: 8px; max-width: 600px; margin: 0 auto; }
                h1 { color: #1B4F72; }
                p  { color: #5D6D7E; line-height: 1.6; }
                .footer { margin-top: 40px; color: #999; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class="container">
                <h1>🦔 Hoş Geldiniz!</h1>
                <p>Merhaba <strong>{$this->name}</strong>,</p>
                <p>Kirpi Framework'e hoş geldiniz! Sisteme başarıyla kayıt oldunuz.</p>
                <p>Herhangi bir sorunuz olursa bizimle iletişime geçebilirsiniz.</p>
                <div class="footer">
                    <p>Bu mail Kirpi Framework tarafından gönderilmiştir.</p>
                </div>
            </div>
        </body>
        </html>
        HTML;
    }
}