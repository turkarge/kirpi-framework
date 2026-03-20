<?php

declare(strict_types=1);

namespace Core\Notification\Channels;

use Core\Notification\Notification;
use Core\Notification\NotificationChannelInterface;

class FcmChannel implements NotificationChannelInterface
{
    private string $serverKey;
    private string $endpoint = 'https://fcm.googleapis.com/fcm/send';

    public function __construct(array $config)
    {
        $this->serverKey = $config['server_key'] ?? '';
    }

    public function send(object $notifiable, Notification $notification): bool
    {
        $data = $notification->toFcm();

        if (empty($data)) return false;

        // Device token al
        $token = $this->getToken($notifiable);

        if (!$token) return false;

        $payload = [
            'to'           => $token,
            'notification' => [
                'title' => $data['title'] ?? '',
                'body'  => $data['body']  ?? '',
                'icon'  => $data['icon']  ?? '',
                'sound' => $data['sound'] ?? 'default',
                'badge' => $data['badge'] ?? null,
                'image' => $data['image'] ?? null,
            ],
            'data'         => $data['data'] ?? [],
            'priority'     => $data['priority'] ?? 'high',
        ];

        return $this->request($payload);
    }

    public function sendToTopic(string $topic, array $data): bool
    {
        $payload = [
            'to'           => "/topics/{$topic}",
            'notification' => $data,
        ];

        return $this->request($payload);
    }

    public function sendToMultiple(array $tokens, array $data): bool
    {
        $payload = [
            'registration_ids' => $tokens,
            'notification'     => $data,
        ];

        return $this->request($payload);
    }

    public function getChannelName(): string
    {
        return 'fcm';
    }

    private function getToken(object $notifiable): ?string
    {
        if (method_exists($notifiable, 'routeNotificationForFcm')) {
            return $notifiable->routeNotificationForFcm();
        }

        return $notifiable->fcm_token
            ?? $notifiable->device_token
            ?? null;
    }

    private function request(array $payload): bool
    {
        $ch = curl_init($this->endpoint);

        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_HTTPHEADER     => [
                'Authorization: key=' . $this->serverKey,
                'Content-Type: application/json',
            ],
        ]);

        $response = curl_exec($ch);
        $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $result = json_decode($response, true);

        return $status === 200 && ($result['success'] ?? 0) > 0;
    }
}