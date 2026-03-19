<?php

declare(strict_types=1);

namespace Core\Routing;

class Route
{
    private array   $methods     = [];
    private string  $uri         = '';
    private mixed   $action      = null;
    private array   $middlewares = [];
    private ?string $name        = null;
    private array   $wheres      = [];
    private array   $parameters  = [];

    public function __construct(
        array  $methods,
        string $uri,
        mixed  $action,
    ) {
        $this->methods = array_map('strtoupper', $methods);
        $this->uri     = $uri;
        $this->action  = $action;
    }

    // ─── Fluent API ──────────────────────────────────────────

    public function middleware(string ...$middlewares): static
    {
        array_push($this->middlewares, ...$middlewares);
        return $this;
    }

    public function name(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function where(string $param, string $pattern): static
    {
        $this->wheres[$param] = $pattern;
        return $this;
    }

    public function whereNumber(string $param): static
    {
        return $this->where($param, '[0-9]+');
    }

    public function whereAlpha(string $param): static
    {
        return $this->where($param, '[a-zA-Z]+');
    }

    public function whereSlug(string $param): static
    {
        return $this->where($param, '[a-z0-9-]+');
    }

    public function whereUuid(string $param): static
    {
        return $this->where($param, '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}');
    }

    // ─── Matching ────────────────────────────────────────────

    public function matches(string $method, string $uri): bool
    {
        if (!in_array($method, $this->methods) && !in_array('HEAD', $this->methods)) {
            if (!in_array($method, $this->methods)) return false;
        }

        return (bool) preg_match($this->compile(), $uri);
    }

    public function matchesUri(string $uri): bool
    {
        return (bool) preg_match($this->compile(), $uri);
    }

    public function extractParameters(string $uri): array
    {
        preg_match($this->compile(), $uri, $matches);

        return array_filter(
            $matches,
            fn($key) => !is_int($key),
            ARRAY_FILTER_USE_KEY
        );
    }

    public function compile(): string
    {
        $pattern = preg_replace_callback(
            '/\{(\w+?)(\?)?\}/',
            function (array $matches) {
                $name     = $matches[1];
                $optional = isset($matches[2]);
                $pattern  = $this->wheres[$name] ?? '[^/]+';

                return $optional
                    ? "(?P<{$name}>{$pattern})?"
                    : "(?P<{$name}>{$pattern})";
            },
            $this->uri
        );

        return '#^' . $pattern . '$#';
    }

    public function generateUrl(array $params = []): string
    {
        $uri = $this->uri;

        foreach ($params as $key => $value) {
            $uri = str_replace(
                ['{' . $key . '}', '{' . $key . '?}'],
                $value,
                $uri
            );
        }

        return preg_replace('/\{[^}]+\?\}/', '', $uri);
    }

    public function setParameters(array $parameters): static
    {
        $this->parameters = $parameters;
        return $this;
    }

    public function getParameter(string $key, mixed $default = null): mixed
    {
        return $this->parameters[$key] ?? $default;
    }

    // ─── Getters ─────────────────────────────────────────────

    public function getMethods(): array     { return $this->methods; }
    public function getUri(): string        { return $this->uri; }
    public function getAction(): mixed      { return $this->action; }
    public function getMiddlewares(): array { return $this->middlewares; }
    public function getName(): ?string      { return $this->name; }
    public function getParameters(): array  { return $this->parameters; }
}