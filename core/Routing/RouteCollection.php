<?php

declare(strict_types=1);

namespace Core\Routing;

use Core\Routing\Exceptions\RouteNotFoundException;
use Core\Routing\Exceptions\MethodNotAllowedException;

class RouteCollection
{
    private array $routes    = [];
    private array $nameIndex = [];

    public function add(Route $route): Route
    {
        $this->routes[] = $route;

        if ($route->getName() !== null) {
            $this->nameIndex[$route->getName()] = $route;
        }

        return $route;
    }

    public function match(string $method, string $uri): Route
    {
        $methodMatches = [];

        foreach ($this->routes as $route) {
            if (!$route->matchesUri($uri)) {
                continue;
            }

            if (!in_array($method, $route->getMethods())) {
                array_push($methodMatches, ...$route->getMethods());
                continue;
            }

            $params = $route->extractParameters($uri);
            return $route->setParameters($params);
        }

        if (!empty($methodMatches)) {
            throw new MethodNotAllowedException(array_unique($methodMatches));
        }

        throw new RouteNotFoundException($uri);
    }

    public function getByName(string $name): ?Route
    {
        return $this->nameIndex[$name] ?? null;
    }

    public function all(): array
    {
        return $this->routes;
    }

    public function count(): int
    {
        return count($this->routes);
    }
}