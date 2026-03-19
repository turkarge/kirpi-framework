<?php

declare(strict_types=1);

namespace Core\Routing;

use Core\Http\Request;
use Core\Http\Response;

class MiddlewarePipeline
{
    private Request $request;
    private array   $middlewares = [];

    public function send(Request $request): static
    {
        $this->request = $request;
        return $this;
    }

    public function through(array $middlewares): static
    {
        $this->middlewares = $middlewares;
        return $this;
    }

    public function then(\Closure $destination): Response
    {
        $pipeline = array_reduce(
            array_reverse($this->middlewares),
            fn($carry, $middleware) => fn(Request $req) => $this->runMiddleware($middleware, $req, $carry),
            $destination
        );

        return $pipeline($this->request);
    }

    private function runMiddleware(string|object $middleware, Request $request, \Closure $next): Response
    {
        [$class, $params] = $this->parseMiddleware($middleware);

        $instance = is_object($class) ? $class : new $class();

        return $instance->handle($request, $next, ...$params);
    }

    private function parseMiddleware(string|object $middleware): array
    {
        if (is_object($middleware)) {
            return [$middleware, []];
        }

        if (str_contains($middleware, ':')) {
            [$class, $paramString] = explode(':', $middleware, 2);
            return [$class, explode(',', $paramString)];
        }

        return [$middleware, []];
    }
}