<?php

declare(strict_types=1);

namespace Core\Providers;

use Core\Auth\AuthManager;
use Core\Database\DatabaseManager;
use Core\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $config = $this->app->make('config')->load('auth');
        $db = $this->app->make(DatabaseManager::class);

        $auth = new AuthManager($config, $db);
        $this->app->instance('auth', $auth);
        $this->app->instance(AuthManager::class, $auth);
    }
}