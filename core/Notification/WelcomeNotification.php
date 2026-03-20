<?php

declare(strict_types=1);

namespace Core\Notification;

class WelcomeNotification extends Notification
{
public function __construct(
    private readonly string $name,
    private readonly string $locale = 'tr',
) {
    parent::__construct();
}

    public function via(): array
    {
        return ['database'];
    }

    public function toFcm(): array
    {
        return [
            'title' => __('notification.welcome.title', [], $this->locale),
            'body'  => __('notification.welcome.body', ['name' => $this->name], $this->locale),
            'data'  => [
                'type'   => 'welcome',
                'action' => 'open_dashboard',
            ],
        ];
    }

    public function toOneSignal(): array
    {
        return [
            'title' => __('notification.welcome.title', [], $this->locale),
            'body'  => __('notification.welcome.body', ['name' => $this->name], $this->locale),
            'data'  => ['type' => 'welcome'],
        ];
    }

    public function toDatabase(): array
    {
        return [
            'type'    => 'welcome',
            'title'   => __('notification.welcome.title', [], $this->locale),
            'message' => __('notification.welcome.body', ['name' => $this->name], $this->locale),
        ];
    }
}