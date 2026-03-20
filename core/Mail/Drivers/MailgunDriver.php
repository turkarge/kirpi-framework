<?php

declare(strict_types=1);

namespace Core\Mail\Drivers;

use Core\Mail\MailDriverInterface;
use Core\Mail\Mailable;

class MailgunDriver implements MailDriverInterface
{
    private string $apiKey;
    private string $domain;
    private string $endpoint;

    public function __construct(array $config)
    {
        $this->apiKey   = $config['api_key'] ?? '';
        $this->domain   = $config['domain']  ?? '';
        $this->endpoint = $config['endpoint'] ?? 'https://api.mailgun.net/v3';
    }

    public function send(Mailable $mailable): bool
    {
        $mailable->build();

        $data = [
            'from'    => $this->formatAddress($mailable->getFrom(), $mailable->getFromName()),
            'to'      => implode(',', array_column($mailable->getTo(), 'email')),
            'subject' => $mailable->getSubject(),
        ];

        if ($mailable->getHtmlBody()) {
            $data['html'] = $mailable->getHtmlBody();
        }

        if ($mailable->getTextBody()) {
            $data['text'] = $mailable->getTextBody();
        }

        if (!empty($mailable->getCc())) {
            $data['cc'] = implode(',', array_column($mailable->getCc(), 'email'));
        }

        if (!empty($mailable->getBcc())) {
            $data['bcc'] = implode(',', array_column($mailable->getBcc(), 'email'));
        }

        $response = $this->post("/messages", $data);

        return isset($response['id']);
    }

    public function getDriverName(): string
    {
        return 'mailgun';
    }

    private function post(string $endpoint, array $data): array
    {
        $url = "{$this->endpoint}/{$this->domain}{$endpoint}";

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $data,
            CURLOPT_USERPWD        => "api:{$this->apiKey}",
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true) ?? [];
    }

    private function formatAddress(string $email, string $name = ''): string
    {
        return $name ? "{$name} <{$email}>" : $email;
    }
}