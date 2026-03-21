<?php

declare(strict_types=1);

namespace Core\Providers;

use Core\Cache\CacheManager;
use Core\Event\EventDispatcher;
use Core\I18n\Translator;
use Core\Storage\StorageManager;
use Core\Support\ServiceProvider;

class SupportServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $config = $this->app->make('config');

        $dispatcher = new EventDispatcher();
        $this->app->instance('events', $dispatcher);
        $this->app->instance(EventDispatcher::class, $dispatcher);

        $storage = new StorageManager($config->load('storage'));
        $this->app->instance('storage', $storage);
        $this->app->instance(StorageManager::class, $storage);

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
    }
}
