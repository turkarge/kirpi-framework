<?php

declare(strict_types=1);

namespace Core\Http;

class Response
{
    private string $content     = '';
    private int    $status      = 200;
    private array  $headers     = [];
    private string $version     = '1.1';

    private static array $statusTexts = [
        200 => 'OK',
        201 => 'Created',
        204 => 'No Content',
        301 => 'Moved Permanently',
        302 => 'Found',
        304 => 'Not Modified',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        409 => 'Conflict',
        419 => 'Page Expired',
        422 => 'Unprocessable Entity',
        429 => 'Too Many Requests',
        500 => 'Internal Server Error',
        503 => 'Service Unavailable',
    ];

    public function __construct(
        string $content = '',
        int    $status  = 200,
        array  $headers = [],
    ) {
        $this->content = $content;
        $this->status  = $status;
        $this->headers = $headers;
    }

    // ─── Factory Methods ─────────────────────────────────────

    public static function make(string $content = '', int $status = 200, array $headers = []): static
    {
        return new static($content, $status, $headers);
    }

    public static function json(mixed $data, int $status = 200, array $headers = []): static
    {
        $headers['Content-Type'] = 'application/json; charset=utf-8';

        return new static(
            json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            $status,
            $headers
        );
    }

    public static function redirect(string $url, int $status = 302): static
    {
        return new static('', $status, ['Location' => $url]);
    }

    public static function noContent(): static
    {
        return new static('', 204);
    }

    public static function notFound(string $message = 'Not Found'): static
    {
        return static::json(['error' => $message], 404);
    }

    public static function unauthorized(string $message = 'Unauthorized'): static
    {
        return static::json(['error' => $message], 401);
    }

    public static function forbidden(string $message = 'Forbidden'): static
    {
        return static::json(['error' => $message], 403);
    }

    public static function unprocessable(array $errors): static
    {
        return static::json(['errors' => $errors], 422);
    }

    public static function serverError(string $message = 'Server Error'): static
    {
        return static::json(['error' => $message], 500);
    }

    // ─── Fluent Setters ──────────────────────────────────────

    public function withContent(string $content): static
    {
        $clone          = clone $this;
        $clone->content = $content;
        return $clone;
    }

    public function withStatus(int $status): static
    {
        $clone         = clone $this;
        $clone->status = $status;
        return $clone;
    }

    public function header(string $key, string $value): static
    {
        $clone                  = clone $this;
        $clone->headers[$key]   = $value;
        return $clone;
    }

    public function withHeaders(array $headers): static
    {
        $clone          = clone $this;
        $clone->headers = array_merge($clone->headers, $headers);
        return $clone;
    }

    public function withCookie(
        string $name,
        string $value,
        int    $expires  = 0,
        string $path     = '/',
        string $domain   = '',
        bool   $secure   = true,
        bool   $httpOnly = true,
        string $sameSite = 'Lax',
    ): static {
        $clone = clone $this;
        $clone->headers['Set-Cookie'] = sprintf(
            '%s=%s; Expires=%s; Path=%s%s%s%s; SameSite=%s',
            urlencode($name),
            urlencode($value),
            $expires > 0 ? gmdate('D, d M Y H:i:s T', $expires) : '',
            $path,
            $domain ? "; Domain={$domain}" : '',
            $secure ? '; Secure' : '',
            $httpOnly ? '; HttpOnly' : '',
            $sameSite
        );
        return $clone;
    }

    // ─── Gönder ──────────────────────────────────────────────

    public function send(): void
    {
        $this->sendHeaders();
        $this->sendContent();
    }

    public function sendHeaders(): void
    {
        if (headers_sent()) {
            return;
        }

        // Status line
        $statusText = static::$statusTexts[$this->status] ?? 'Unknown';
        header("HTTP/{$this->version} {$this->status} {$statusText}", true, $this->status);

        // Headers
        foreach ($this->headers as $key => $value) {
            header("{$key}: {$value}", true);
        }
    }

    public function sendContent(): void
    {
        echo $this->content;
    }

    // ─── Getters ─────────────────────────────────────────────

    public function getContent(): string  { return $this->content; }
    public function getStatus(): int      { return $this->status; }
    public function getHeaders(): array   { return $this->headers; }

    public function isOk(): bool          { return $this->status === 200; }
    public function isRedirect(): bool    { return $this->status >= 300 && $this->status < 400; }
    public function isClientError(): bool { return $this->status >= 400 && $this->status < 500; }
    public function isServerError(): bool { return $this->status >= 500; }
    public function isSuccessful(): bool  { return $this->status >= 200 && $this->status < 300; }
}