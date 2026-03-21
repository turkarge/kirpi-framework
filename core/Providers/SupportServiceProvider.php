<?php

declare(strict_types=1);

namespace Core\Providers;

use Core\Cache\CacheManager;
use Core\Database\DatabaseManager;
use Core\Event\EventDispatcher;
use Core\I18n\Translator;
use Core\Mail\MailManager;
use Core\Monitor\HealthChecker;
use Core\Monitor\MetricsCollector;
use Core\Monitor\MonitorController;
use Core\Notification\NotificationManager;
use Core\Queue\QueueManager;
use Core\Storage\StorageManager;
use Core\Support\ServiceProvider;

class SupportServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $config = $this->app->make('config');
        $db = $this->app->make(DatabaseManager::class);

        $dispatcher = new EventDispatcher();
        $this->app->instance('events', $dispatcher);
        $this->app->instance(EventDispatcher::class, $dispatcher);

        $storage = new StorageManager($config->load('storage'));
        $this->app->instance('storage', $storage);
        $this->app->instance(StorageManager::class, $storage);

        $notification = new NotificationManager($config->load('notification'), $db);
        $this->app->instance('notification', $notification);
        $this->app->instance(NotificationManager::class, $notification);

        $mail = new MailManager($config->load('mail'));
        $this->app->instance('mail', $mail);
        $this->app->instance(MailManager::class, $mail);

        $queue = new QueueManager($config->load('queue'), $db);
        $this->app->instance('queue', $queue);
        $this->app->instance(QueueManager::class, $queue);

        $translator = new Translator(
            locale: $config->get('app.locale', 'tr'),
            fallback: $config->get('app.fallback_locale', 'en'),
            path: base_path('lang'),
        );
        $this->app->instance('translator', $translator);
        $this->app->instance(Translator::class, $translator);

        $cache = new CacheManager($config->load('cache'));
        $this->app->instance('cache', $cache);
        $this->app->instance(CacheManager::class, $cache);

        $this->app->singleton(HealthChecker::class, fn() => new HealthChecker($db, $cache));
        $this->app->singleton(MetricsCollector::class, fn() => new MetricsCollector($db));
        $this->app->singleton(MonitorController::class, fn() => new MonitorController(
            app(HealthChecker::class),
            app(MetricsCollector::class),
        ));
    }
}