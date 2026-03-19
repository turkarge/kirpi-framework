<?php

declare(strict_types=1);

namespace Core\Routing;

use Core\Http\Request;
use Core\Http\Response;

class Router
{
    private RouteCollection $routes;
    private array           $groupStack = [];

    public function __construct()
    {
        $this->routes = new RouteCollection();
    }

    // ─── HTTP Methods ─────────────────────────────────────────

    public function get(string $uri, mixed $action): Route
    {
        return $this->addRoute(['GET', 'HEAD'], $uri, $action);
    }

    public function post(string $uri, mixed $action): Route
    {
        return $this->addRoute(['POST'], $uri, $action);
    }

    public function put(string $uri, mixed $action): Route
    {
        return $this->addRoute(['PUT'], $uri, $action);
    }

    public function patch(string $uri, mixed $action): Route
    {
        return $this->addRoute(['PATCH'], $uri, $action);
    }

    public function delete(string $uri, mixed $action): Route
    {
        return $this->addRoute(['DELETE'], $uri, $action);
    }

    public function options(string $uri, mixed $action): Route
    {
        return $this->addRoute(['OPTIONS'], $uri, $action);
    }

    public function any(string $uri, mixed $action): Route
    {
        return $this->addRoute(['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE'], $uri, $action);
    }

    public function match(array $methods, string $uri, mixed $action): Route
    {
        return $this->addRoute($methods, $uri, $action);
    }

    // ─── Resource ────────────────────────────────────────────

    public function resource(string $name, string $controller, array $only = []): void
    {
        $resourceRoutes = [
            'index'   => ['GET',    "/{$name}",           'index'],
            'create'  => ['GET',    "/{$name}/create",    'create'],
            'store'   => ['POST',   "/{$name}",           'store'],
            'show'    => ['GET',    "/{$name}/{id}",      'show'],
            'edit'    => ['GET',    "/{$name}/{id}/edit", 'edit'],
            'update'  => ['PUT',    "/{$name}/{id}",      'update'],
            'destroy' => ['DELETE', "/{$name}/{id}",      'destroy'],
        ];

        $selected = empty($only)
            ? $resourceRoutes
            : array_intersect_key($resourceRoutes, array_flip($only));

        foreach ($selected as $routeName => [$method, $uri, $action]) {
            $this->addRoute([$method], $uri, [$controller, $action])
                 ->name("{$name}.{$routeName}")
                 ->whereNumber('id');
        }
    }

    public function apiResource(string $name, string $controller, array $only = []): void
    {
        $this->resource(
            $name,
            $controller,
            $only ?: ['index', 'store', 'show', 'update', 'destroy']
        );
    }

    // ─── Group ───────────────────────────────────────────────

    public function group(array|string $attributes, \Closure $callback): void
    {
        if (is_string($attributes)) {
            $attributes = ['prefix' => $attributes];
        }

        $this->groupStack[] = $attributes;
        $callback($this);
        array_pop($this->groupStack);
    }

    // ─── Dispatch ────────────────────────────────────────────

    public function dispatch(Request $request): Response
    {
        $route = $this->routes->match(
            $request->method(),
            $request->path()
        );

        $request->setRoute($route);

        $middlewares = $this->resolveMiddlewares($route);

        return (new MiddlewarePipeline())
            ->send($request)
            ->through($middlewares)
            ->then(fn($req) => (new Dispatcher())->dispatch($route, $req));
    }

    // ─── URL Generation ──────────────────────────────────────

    public function url(string $name, array $params = []): string
    {
        $route = $this->routes->getByName($name)
            ?? throw new \RuntimeException("Route [{$name}] not found.");

        return $route->generateUrl($params);
    }

    public function getRoutes(): RouteCollection
    {
        return $this->routes;
    }

    // ─── Load Routes ─────────────────────────────────────────

    public function loadRoutes(string $path): void
    {
        $router = $this;
        require $path;
    }

    // ─── Private ─────────────────────────────────────────────

    private function addRoute(array $methods, string $uri, mixed $action): Route
    {
        $uri         = $this->applyGroupPrefix($uri);
        $middlewares = $this->applyGroupMiddlewares();
        $namePrefix  = $this->applyGroupNamePrefix();

        $route = new Route($methods, $uri, $action);

        if (!empty($middlewares)) {
            $route->middleware(...$middlewares);
        }

        if ($namePrefix !== '') {
            // Prefix route ismini sonradan name() ile ekleyince prefix'i uygula
        }

        return $this->routes->add($route);
    }

    private function applyGroupPrefix(string $uri): string
    {
        $prefix = implode('', array_column($this->groupStack, 'prefix'));

        if (empty($prefix)) return '/' . ltrim($uri, '/');

        return rtrim($prefix, '/') . '/' . ltrim($uri, '/');
    }

    private function applyGroupMiddlewares(): array
    {
        return array_merge(...array_map(
            fn($group) => (array) ($group['middleware'] ?? []),
            $this->groupStack
        ));
    }

    private function applyGroupNamePrefix(): string
    {
        return implode('', array_column($this->groupStack, 'as'));
    }

    private function resolveMiddlewares(Route $route): array
    {
        $map = config('middleware.aliases', []);

        return array_map(
            fn($alias) => $map[$alias] ?? $alias,
            $route->getMiddlewares()
        );
    }
}