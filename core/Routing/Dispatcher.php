<?php

declare(strict_types=1);

namespace Core\Routing;

use Core\Http\Request;
use Core\Http\Response;

class Dispatcher
{
    public function dispatch(Route $route, Request $request): Response
    {
        $action = $route->getAction();

        if ($action instanceof \Closure) {
            return $this->callClosure($action, $route, $request);
        }

        if (is_array($action)) {
            [$controllerClass, $method] = $action;
            return $this->callController($controllerClass, $method, $route, $request);
        }

        if (is_string($action) && str_contains($action, '@')) {
            [$controllerClass, $method] = explode('@', $action, 2);
            return $this->callController($controllerClass, $method, $route, $request);
        }

        throw new \RuntimeException('Invalid route action.');
    }

    private function callController(string $class, string $method, Route $route, Request $request): Response
    {
        if (!class_exists($class)) {
            throw new \RuntimeException("Controller [{$class}] not found.");
        }

        if (!method_exists($class, $method)) {
            throw new \RuntimeException("Method [{$method}] not found in [{$class}].");
        }

        $controller = app($class);
        $params     = $this->resolveMethodParams($class, $method, $route, $request);

        return $controller->$method(...$params);
    }

    private function callClosure(\Closure $closure, Route $route, Request $request): Response
    {
        $params = array_values($route->getParameters());
        return $closure($request, ...$params);
    }

    private function resolveMethodParams(
        string  $class,
        string  $method,
        Route   $route,
        Request $request
    ): array {
        $reflection = new \ReflectionMethod($class, $method);
        $params     = [];

        foreach ($reflection->getParameters() as $param) {
            $type = $param->getType()?->getName();

            if ($type === Request::class) {
                $params[] = $request;
                continue;
            }

            if ($route->getParameter($param->getName()) !== null) {
                $params[] = $route->getParameter($param->getName());
                continue;
            }

            if ($type && class_exists($type)) {
                $params[] = app($type);
                continue;
            }

            if ($param->isDefaultValueAvailable()) {
                $params[] = $param->getDefaultValue();
            }
        }

        return $params;
    }
}