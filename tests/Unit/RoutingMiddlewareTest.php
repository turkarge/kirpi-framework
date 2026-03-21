<?php

declare(strict_types=1);

namespace Tests\Unit;

use Core\Http\Request;
use Core\Http\Response;
use Core\Routing\Router;
use Tests\Support\TestCase;

class RoutingMiddlewareTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        app('config')->set('middleware.aliases.tag', TestTagMiddleware::class);
        app('config')->set('middleware.global', ['tag:global']);
        app('config')->set('middleware.groups.api', ['tag:api-1', 'tag:api-2']);

        TestTagMiddleware::$calls = [];
    }

    public function test_middlewares_are_resolved_in_global_group_route_order(): void
    {
        $router = new Router();

        $router->get('/_mw-order', fn() => Response::make('ok'))
            ->middleware('api', 'tag:route');

        $response = $router->dispatch(new Request(server: [
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/_mw-order',
        ]));

        $this->assertSame(200, $response->getStatus());
        $this->assertSame(['global', 'api-1', 'api-2', 'route'], TestTagMiddleware::$calls);
    }

    public function test_load_routes_attributes_apply_group_middleware(): void
    {
        $router = new Router();

        $tmpPath = tempnam(sys_get_temp_dir(), 'kirpi-route-');
        $routeFile = $tmpPath . '.php';
        rename($tmpPath, $routeFile);

        file_put_contents($routeFile, <<<'PHP'
<?php
/** @var \Core\Routing\Router $router */
$router->get('/_mw-load', fn() => \Core\Http\Response::make('ok'));
PHP
        );

        $router->loadRoutes($routeFile, ['middleware' => 'api']);

        $response = $router->dispatch(new Request(server: [
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/_mw-load',
        ]));

        @unlink($routeFile);

        $this->assertSame(200, $response->getStatus());
        $this->assertSame(['global', 'api-1', 'api-2'], TestTagMiddleware::$calls);
    }
}

class TestTagMiddleware
{
    public static array $calls = [];

    public function handle(Request $request, \Closure $next, string $name): Response
    {
        self::$calls[] = $name;

        return $next($request);
    }
}