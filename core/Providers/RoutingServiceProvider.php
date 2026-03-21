<?php

declare(strict_types=1);

namespace Core\Providers;

use Core\Routing\Router;
use Core\Support\ServiceProvider;

class RoutingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $router = new Router();

        $this->app->instance('router', $router);
        $this->app->instance(Router::class, $router);
    }
}