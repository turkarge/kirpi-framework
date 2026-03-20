<?php

declare(strict_types=1);

namespace Core\Notification;

interface NotificationChannelInterface
{
    public function send(object $notifiable, Notification $notification): bool;
    public function getChannelName(): string;
}