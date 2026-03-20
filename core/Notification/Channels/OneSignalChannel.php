<?php

declare(strict_types=1);

namespace Core\Notification\Channels;

use Core\Notification\Notification;
use Core\Notification\NotificationChannelInterface;

class OneSignalChannel implements NotificationChannelInterface
{
    private string $appId;
    private string $apiKey;
    private string $endpoint = 'https://onesignal.com/api/v1/notifications';

    public function __construct(array $config)
    {
        $this->appId  = $config['app_id']  ?? '';
        $this->apiKey = $config['api_key'] ?? '';
    }

    public function send(object $notifiable, Notification $notification): bool
    {
        $data = $notification->toOneSignal();

        if (empty($data)) return false;

        $playerId = $this->getPlayerId($notifiable);

        $payload = [
            'app_id'            => $this->appId,
            'headings'          => ['en' => $data['title'] ?? ''],
            'contents'          => ['en' => $data['body']  ?? ''],
            'data'              => $data['data'] ?? [],
            'include_player_ids'=> $playerId ? [$playerId] : [],
        ];

        // Segment'e gönder
        if (isset($data['segment'])) {
            $payload['included_segments'] = [$data['segment']];
            unset($payload['include_player_ids']);
        }

        // Büyük ikon
        if (isset($data['large_icon'])) {
            $payload['large_icon'] = $data['large_icon'];
        }

        // Resim
        if (isset($data['big_picture'])) {
            $payload['big_picture'] = $data['big_picture'];
        }

        // Aksiyon butonları
        if (isset($data['buttons'])) {
            $payload['buttons'] = $data['buttons'];
        }

        // Zamanlama
        if (isset($data['send_after'])) {
            $payload['send_after'] = $data['send_after'];
        }

        return $this->request($payload);
    }

    public function getChannelName(): string
    {
        return 'onesignal';
    }

    private function getPlayerId(object $notifiable): ?string
    {
        if (method_exists($notifiable, 'routeNotificationForOneSignal')) {
            return $notifiable->routeNotificationForOneSignal();
        }

        return $notifiable->onesignal_player_id
            ?? $notifiable->push_token
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
                'Authorization: Basic ' . $this->apiKey,
                'Content-Type: application/json',
            ],
        ]);

        $response = curl_exec($ch);
        $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $result = json_decode($response, true);

        return $status === 200 && isset($result['id']);
    }
}