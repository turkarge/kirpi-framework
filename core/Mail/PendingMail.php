<?php

declare(strict_types=1);

namespace Core\Mail;

class PendingMail
{
    private array $to      = [];
    private array $cc      = [];
    private array $bcc     = [];

    public function __construct(
        private readonly MailManager $manager,
    ) {}

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

    public function send(Mailable $mailable): bool
    {
        // Alıcıları mailable'a aktar
        foreach ($this->to as $recipient) {
            $mailable->to($recipient['email'], $recipient['name']);
        }

        foreach ($this->cc as $cc) {
            $mailable->cc($cc['email'], $cc['name']);
        }

        foreach ($this->bcc as $bcc) {
            $mailable->bcc($bcc['email'], $bcc['name']);
        }

        return $this->manager->send($mailable);
    }

    public function queue(Mailable $mailable): string
    {
        foreach ($this->to as $recipient) {
            $mailable->to($recipient['email'], $recipient['name']);
        }

        return $this->manager->later(0, $mailable);
    }
}