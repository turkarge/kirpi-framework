<?php

declare(strict_types=1);

namespace Core\Providers;

use Core\Database\DatabaseManager;
use Core\Mail\MailManager;
use Core\Notification\NotificationManager;
use Core\Queue\QueueManager;
use Core\Support\ServiceProvider;

class CommunicationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $config = $this->app->make('config');
        $db = $this->app->make(DatabaseManager::class);

        $notification = new NotificationManager($config->load('notification'), $db);
        $this->app->instance('notification', $notification);
        $this->app->instance(NotificationManager::class, $notification);

        $mail = new MailManager($config->load('mail'));
        $this->app->instance('mail', $mail);
        $this->app->instance(MailManager::class, $mail);

        $queue = new QueueManager($config->load('queue'), $db);
        $this->app->instance('queue', $queue);
        $this->app->instance(QueueManager::class, $queue);
    }
}