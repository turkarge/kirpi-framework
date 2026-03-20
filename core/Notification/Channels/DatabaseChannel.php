<?php

declare(strict_types=1);

namespace Core\Notification\Channels;

use Core\Notification\Notification;
use Core\Notification\NotificationChannelInterface;
use Core\Database\DatabaseManager;

class DatabaseChannel implements NotificationChannelInterface
{
    public function __construct(
        private readonly DatabaseManager $db,
    ) {}

public function send(object $notifiable, Notification $notification): bool
{
    $data = $notification->toDatabase();

    if (empty($data)) return false;

    $this->db->table('notifications')->insert([
        'id'              => $notification->getId(),
        'notifiable_type' => get_class($notifiable),
        'notifiable_id'   => $notifiable->getKey(),
        'type'            => get_class($notification),
        'data'            => json_encode($data),
        'read_at'         => null,
        'created_at'      => now(),
        'updated_at'      => now(),
    ]);

    return true;
}

    public function getChannelName(): string
    {
        return 'database';
    }
}