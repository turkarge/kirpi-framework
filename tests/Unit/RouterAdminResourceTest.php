<?php

declare(strict_types=1);

namespace Tests\Unit;

use Core\Http\Response;
use Core\Routing\Router;
use PHPUnit\Framework\TestCase;

class RouterAdminResourceTest extends TestCase
{
    public function test_admin_resource_applies_auth_and_permission_middlewares(): void
    {
        $router = new Router();
        $router->adminResource('products', \Tests\Unit\FakeCrudController::class);

        $routes = $router->getRoutes()->all();
        $this->assertNotEmpty($routes);

        foreach ($routes as $route) {
            $middlewares = $route->getMiddlewares();
            $this->assertContains('auth', $middlewares);
            $this->assertContains('permission:admin-access', $middlewares);
            $this->assertStringStartsWith('/admin/products', $route->getUri());
            $this->assertNotNull($route->getName());
            $this->assertStringStartsWith('admin.products.', (string) $route->getName());
        }
    }
}

class FakeCrudController
{
    public function index(): Response { return Response::json([]); }
    public function create(): Response { return Response::json([]); }
    public function store(): Response { return Response::json([]); }
    public function show(): Response { return Response::json([]); }
    public function edit(): Response { return Response::json([]); }
    public function update(): Response { return Response::json([]); }
    public function destroy(): Response { return Response::json([]); }
}

