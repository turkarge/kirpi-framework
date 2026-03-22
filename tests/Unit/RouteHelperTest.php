<?php

declare(strict_types=1);

namespace Tests\Unit;

use Core\Http\Response;
use Core\Routing\Router;
use Tests\Support\TestCase;

class RouteHelperTest extends TestCase
{
    public function test_route_helper_generates_named_route_url(): void
    {
        $router = new Router();
        $router->get('/tests/orders/{id}', fn () => Response::make('ok'))
            ->name('tests.orders.show')
            ->whereNumber('id');

        app()->instance(Router::class, $router);
        app()->instance('router', $router);

        $url = route('tests.orders.show', ['id' => 42]);

        $this->assertSame('/tests/orders/42', $url);
    }
}

