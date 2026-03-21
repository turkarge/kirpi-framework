<?php

declare(strict_types=1);

namespace Core\Routing;

use Core\Http\Request;
use Core\Http\Response;

class Router
{
    private RouteCollection $routes;
    private array $groupStack = [];

    public function __construct()
    {
        $this->routes = new RouteCollection();
    }

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

    public function resource(string $name, string $controller, array $only = []): void
    {
        $resourceRoutes = [
            'index' => ['GET', "/{$name}", 'index'],
            'create' => ['GET', "/{$name}/create", 'create'],
            'store' => ['POST', "/{$name}", 'store'],
            'show' => ['GET', "/{$name}/{id}", 'show'],
            'edit' => ['GET', "/{$name}/{id}/edit", 'edit'],
            'update' => ['PUT', "/{$name}/{id}", 'update'],
            'destroy' => ['DELETE', "/{$name}/{id}", 'destroy'],
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

    public function group(array|string $attributes, \Closure $callback): void
    {
        if (is_string($attributes)) {
            $attributes = ['prefix' => $attributes];
        }

        $this->groupStack[] = $attributes;
        $callback($this);
        array_pop($this->groupStack);
    }

    public function dispatch(Request $request): Response
    {
        if (function_exists('app')) {
            app()->instance(Request::class, $request);

            try {
                app(\Core\Auth\AuthManager::class)->clearContext();
            } catch (\Throwable) {
                // Auth manager may not be available in minimal bootstrap paths.
            }
        }

        try {
            $route = $this->routes->match($request->method(), $request->path());

            $request->setRoute($route);

            $middlewares = $this->resolveMiddlewares($route);

            return (new MiddlewarePipeline())
                ->send($request)
                ->through($middlewares)
                ->then(fn($req) => (new Dispatcher())->dispatch($route, $req));
        } catch (\Throwable $e) {
            if (function_exists('app')) {
                try {
                    return app(\Core\Exception\Handler::class)->handle($e, $request);
                } catch (\Throwable) {
                    // Fall through and rethrow original exception.
                }
            }

            throw $e;
        }
    }

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

    public function loadRoutes(string $path, array $attributes = []): void
    {
        $loader = function (Router $router) use ($path): void {
            require $path;
        };

        if ($attributes === []) {
            $loader($this);
            return;
        }

        $this->group($attributes, $loader);
    }

    private function addRoute(array $methods, string $uri, mixed $action): Route
    {
        $uri = $this->applyGroupPrefix($uri);
        $middlewares = $this->applyGroupMiddlewares();
        $namePrefix = $this->applyGroupNamePrefix();

        $route = new Route($methods, $uri, $action);

        if (!empty($middlewares)) {
            $route->middleware(...$middlewares);
        }

        if ($namePrefix !== '') {
            $route->setNamePrefix($namePrefix);
        }

        return $this->routes->add($route);
    }

    private function applyGroupPrefix(string $uri): string
    {
        $prefix = implode('', array_column($this->groupStack, 'prefix'));

        if ($prefix === '') {
            return '/' . ltrim($uri, '/');
        }

        $combined = rtrim($prefix, '/') . '/' . ltrim($uri, '/');

        return '/' . trim($combined, '/');
    }

    private function applyGroupMiddlewares(): array
    {
        if ($this->groupStack === []) {
            return [];
        }

        return array_merge(...array_map(
            fn(array $group) => (array) ($group['middleware'] ?? []),
            $this->groupStack
        ));
    }

    private function applyGroupNamePrefix(): string
    {
        return implode('', array_column($this->groupStack, 'as'));
    }

    private function resolveMiddlewares(Route $route): array
    {
        $aliases = [];
        $global = [];
        $groups = [];

        try {
            $aliases = (array) config('middleware.aliases', []);
            $global = (array) config('middleware.global', []);
            $groups = (array) config('middleware.groups', []);
        } catch (\Throwable) {
            // Continue with only route-defined middlewares.
        }

        $stack = array_merge($global, $route->getMiddlewares());
        $stack = $this->expandMiddlewareGroups($stack, $groups);

        return array_map(
            fn(string $middleware) => $this->resolveMiddlewareAlias($middleware, $aliases),
            $stack
        );
    }

    private function expandMiddlewareGroups(array $middlewares, array $groups): array
    {
        $expanded = [];

        foreach ($middlewares as $middleware) {
            [$name, $params] = $this->splitMiddleware((string) $middleware);

            if (isset($groups[$name])) {
                $expanded = array_merge(
                    $expanded,
                    $this->expandMiddlewareGroups((array) $groups[$name], $groups)
                );
                continue;
            }

            $expanded[] = $params !== null
                ? "{$name}:{$params}"
                : $name;
        }

        return $expanded;
    }

    private function resolveMiddlewareAlias(string $middleware, array $aliases): string
    {
        [$name, $params] = $this->splitMiddleware($middleware);

        if (!isset($aliases[$name])) {
            return $middleware;
        }

        if ($params === null) {
            return $aliases[$name];
        }

        return $aliases[$name] . ':' . $params;
    }

    private function splitMiddleware(string $middleware): array
    {
        if (!str_contains($middleware, ':')) {
            return [$middleware, null];
        }

        return explode(':', $middleware, 2);
    }
}
