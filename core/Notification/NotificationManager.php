<?php

declare(strict_types=1);

namespace Core\Notification;

use Core\Notification\Channels\FcmChannel;
use Core\Notification\Channels\OneSignalChannel;
use Core\Notification\Channels\DatabaseChannel;
use Core\Database\DatabaseManager;

class NotificationManager
{
    private array $channels  = [];
    private array $resolved  = [];

    public function __construct(
        private readonly array           $config,
        private readonly DatabaseManager $db,
    ) {
        $this->registerDefaultChannels();
    }

    // ─── Channel Kayıt ───────────────────────────────────────

    public function registerChannel(string $name, string $class): void
    {
        $this->channels[$name] = $class;
    }

    public function channel(string $name): NotificationChannelInterface
    {
        if (isset($this->resolved[$name])) {
            return $this->resolved[$name];
        }

        $config = $this->config['channels'][$name] ?? [];

        $class = $this->channels[$name]
            ?? throw new \InvalidArgumentException("Notification channel [{$name}] not registered.");

        return $this->resolved[$name] = match($name) {
            'database' => new DatabaseChannel($this->db),
            default    => new $class($config),
        };
    }

    // ─── Send ────────────────────────────────────────────────

    public function send(object $notifiable, Notification $notification): void
    {
        foreach ($notification->via() as $channelName) {
            try {
                $channel = $this->channel($channelName);
                $channel->send($notifiable, $notification);
            } catch (\Throwable $e) {
                app(\Core\Logging\Logger::class)->error(
                    "Notification failed on channel [{$channelName}]",
                    [
                        'notification' => get_class($notification),
                        'notifiable'   => get_class($notifiable),
                        'error'        => $e->getMessage(),
                    ]
                );
            }
        }
    }

    public function sendNow(object $notifiable, Notification $notification): void
    {
        $this->send($notifiable, $notification);
    }

    // ─── Private ─────────────────────────────────────────────

    private function registerDefaultChannels(): void
    {
        $this->channels = [
            'fcm'       => FcmChannel::class,
            'onesignal' => OneSignalChannel::class,
            'database'  => DatabaseChannel::class,
        ];
    }
}