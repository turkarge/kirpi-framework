<?php

declare(strict_types=1);

namespace Core\Notification;

abstract class Notification
{
    private string $id;

    public function __construct()
    {
        $this->id = bin2hex(random_bytes(16));
    }

    public function getId(): string
    {
        return $this->id;
    }

    // Hangi kanallardan gönderilecek
    abstract public function via(): array;

    // FCM için
    public function toFcm(): array
    {
        return [];
    }

    // OneSignal için
    public function toOneSignal(): array
    {
        return [];
    }

    // Web Push için
    public function toWebPush(): array
    {
        return [];
    }

    // APNs için
    public function toApns(): array
    {
        return [];
    }

    // Database için
    public function toDatabase(): array
    {
        return [];
    }
}