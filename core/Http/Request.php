<?php

declare(strict_types=1);

namespace Core\Http;

class Request
{
    private array  $query      = [];
    private array  $post       = [];
    private array  $files      = [];
    private array  $server     = [];
    private array  $headers    = [];
    private array  $cookies    = [];
    private ?array $jsonBody   = null;
    private ?string $rawBody   = null;
    private ?\Core\Routing\Route $route = null;

    public function __construct(
        array $query   = [],
        array $post    = [],
        array $files   = [],
        array $server  = [],
        array $cookies = [],
    ) {
        $this->query   = $query;
        $this->post    = $post;
        $this->files   = $files;
        $this->server  = $server;
        $this->cookies = $cookies;
        $this->headers = $this->parseHeaders($server);
    }

    // ─── Factory ─────────────────────────────────────────────

    public static function capture(): static
    {
        return new static(
            query:   $_GET,
            post:    $_POST,
            files:   $_FILES,
            server:  $_SERVER,
            cookies: $_COOKIE,
        );
    }

    // ─── Method & URI ─────────────────────────────────────────

    public function method(): string
    {
        $method = strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');

        if ($method === 'POST') {
            $override = $this->post['_method']
                ?? $this->header('X-HTTP-Method-Override')
                ?? null;

            if ($override !== null) {
                return strtoupper($override);
            }
        }

        return $method;
    }

    public function isMethod(string $method): bool
    {
        return $this->method() === strtoupper($method);
    }

    public function uri(): string
    {
        return $this->server['REQUEST_URI'] ?? '/';
    }

    public function path(): string
    {
        $path = parse_url($this->uri(), PHP_URL_PATH) ?? '/';
        return '/' . trim($path, '/');
    }

    public function fullUrl(): string
    {
        $scheme = $this->isSecure() ? 'https' : 'http';
        $host   = $this->server['HTTP_HOST'] ?? 'localhost';
        return $scheme . '://' . $host . $this->uri();
    }

    public function isSecure(): bool
    {
        return ($this->server['HTTPS'] ?? 'off') !== 'off';
    }

    // ─── Input ───────────────────────────────────────────────

    public function all(): array
    {
        return array_merge($this->query, $this->post, $this->json() ?? []);
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return data_get($this->all(), $key, $default);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    public function only(string ...$keys): array
    {
        return array_intersect_key($this->all(), array_flip($keys));
    }

    public function except(string ...$keys): array
    {
        return array_diff_key($this->all(), array_flip($keys));
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->all());
    }

    public function filled(string $key): bool
    {
        $value = $this->input($key);
        return $value !== null && $value !== '';
    }

    public function missing(string $key): bool
    {
        return !$this->has($key);
    }

    public function boolean(string $key, bool $default = false): bool
    {
        return filter_var(
            $this->input($key, $default),
            FILTER_VALIDATE_BOOLEAN,
            FILTER_NULL_ON_FAILURE
        ) ?? $default;
    }

    public function integer(string $key, int $default = 0): int
    {
        return (int) $this->input($key, $default);
    }

    public function float(string $key, float $default = 0.0): float
    {
        return (float) $this->input($key, $default);
    }

    public function string(string $key, string $default = ''): string
    {
        return (string) $this->input($key, $default);
    }

    // ─── JSON ────────────────────────────────────────────────

    public function json(?string $key = null, mixed $default = null): mixed
    {
        if ($this->jsonBody === null) {
            $raw = $this->rawBody ?? file_get_contents('php://input') ?? '';

            if (!empty($raw) && $this->isJson()) {
                $this->jsonBody = json_decode($raw, true) ?? [];
            } else {
                $this->jsonBody = [];
            }
        }

        if ($key === null) {
            return $this->jsonBody;
        }

        return data_get($this->jsonBody, $key, $default);
    }

    public function isJson(): bool
    {
        $contentType = $this->header('Content-Type') ?? '';
        return str_contains($contentType, 'application/json');
    }

    public function expectsJson(): bool
    {
        $accept = $this->header('Accept') ?? '';
        return str_contains($accept, 'application/json')
            || str_contains($accept, '*/*')
            || $this->isJson();
    }

    public function wantsJson(): bool
    {
        return $this->expectsJson();
    }

    // ─── Headers ─────────────────────────────────────────────

    public function header(string $key, mixed $default = null): mixed
    {
        $key = strtolower(str_replace('_', '-', $key));
        return $this->headers[$key] ?? $default;
    }

    public function headers(): array
    {
        return $this->headers;
    }

    public function bearerToken(): ?string
    {
        $auth = $this->header('Authorization') ?? '';

        if (str_starts_with($auth, 'Bearer ')) {
            return substr($auth, 7);
        }

        return null;
    }

    // ─── Files ───────────────────────────────────────────────

    public function file(string $key): ?UploadedFile
    {
        if (!isset($this->files[$key])) {
            return null;
        }

        return new UploadedFile($this->files[$key]);
    }

    public function hasFile(string $key): bool
    {
        return isset($this->files[$key])
            && $this->files[$key]['error'] === UPLOAD_ERR_OK;
    }

    // ─── Cookies ─────────────────────────────────────────────

    public function cookie(string $key, mixed $default = null): mixed
    {
        return $this->cookies[$key] ?? $default;
    }

    // ─── Server ──────────────────────────────────────────────

    public function server(string $key, mixed $default = null): mixed
    {
        return $this->server[$key] ?? $default;
    }

    public function ip(): string
    {
        return $this->server['HTTP_X_FORWARDED_FOR']
            ?? $this->server['HTTP_CLIENT_IP']
            ?? $this->server['REMOTE_ADDR']
            ?? '0.0.0.0';
    }

    public function userAgent(): string
    {
        return $this->server['HTTP_USER_AGENT'] ?? '';
    }

    public function isAjax(): bool
    {
        return $this->header('X-Requested-With') === 'XMLHttpRequest';
    }

    // ─── Route ───────────────────────────────────────────────

    public function setRoute(\Core\Routing\Route $route): void
    {
        $this->route = $route;
    }

    public function route(?string $param = null, mixed $default = null): mixed
    {
        if ($param === null) {
            return $this->route;
        }

        return $this->route?->getParameter($param, $default);
    }

    // ─── Validation ──────────────────────────────────────────

public function validate(array $rules): array
{
    return app(\Core\Validation\Validator::class)
        ->validate($this->all(), $rules);
}

    // ─── Helpers ─────────────────────────────────────────────

    private function parseHeaders(array $server): array
    {
        $headers = [];

        foreach ($server as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $header = strtolower(str_replace('_', '-', substr($key, 5)));
                $headers[$header] = $value;
            } elseif (in_array($key, ['CONTENT_TYPE', 'CONTENT_LENGTH', 'CONTENT_MD5'])) {
                $header = strtolower(str_replace('_', '-', $key));
                $headers[$header] = $value;
            }
        }

        return $headers;
    }
}