<?php

declare(strict_types=1);

namespace Core\Http\Client;

class Response
{
    private ?array $decodedJson = null;

    public function __construct(
        private readonly int    $status,
        private readonly array  $headers,
        private readonly string $body,
        private readonly array  $info = [],
    ) {}

    // ─── Status ──────────────────────────────────────────────

    public function status(): int          { return $this->status; }
    public function ok(): bool             { return $this->status === 200; }
    public function created(): bool        { return $this->status === 201; }
    public function noContent(): bool      { return $this->status === 204; }
    public function badRequest(): bool     { return $this->status === 400; }
    public function unauthorized(): bool   { return $this->status === 401; }
    public function forbidden(): bool      { return $this->status === 403; }
    public function notFound(): bool       { return $this->status === 404; }
    public function unprocessable(): bool  { return $this->status === 422; }
    public function tooManyRequests(): bool { return $this->status === 429; }
    public function serverError(): bool    { return $this->status >= 500; }
    public function successful(): bool     { return $this->status >= 200 && $this->status < 300; }
    public function redirect(): bool       { return $this->status >= 300 && $this->status < 400; }
    public function clientError(): bool    { return $this->status >= 400 && $this->status < 500; }
    public function failed(): bool         { return $this->clientError() || $this->serverError(); }

    // ─── Body ────────────────────────────────────────────────

    public function body(): string { return $this->body; }

    public function json(?string $key = null, mixed $default = null): mixed
    {
        $this->decodedJson ??= json_decode($this->body, true) ?? [];

        if ($key === null) {
            return $this->decodedJson;
        }

        return data_get($this->decodedJson, $key, $default);
    }

    public function object(): object
    {
        return (object) ($this->json() ?? []);
    }

    public function collect(): \Core\Database\Result\Collection
    {
        return new \Core\Database\Result\Collection($this->json() ?? []);
    }

    // ─── Headers ─────────────────────────────────────────────

    public function header(string $key): ?string
    {
        return $this->headers[$key]
            ?? $this->headers[strtolower($key)]
            ?? null;
    }

    public function headers(): array { return $this->headers; }

    public function remainingRequests(): ?int
    {
        $value = $this->header('X-RateLimit-Remaining');
        return $value !== null ? (int) $value : null;
    }

    public function retryAfter(): ?int
    {
        $value = $this->header('Retry-After');
        return $value !== null ? (int) $value : null;
    }

    // ─── Throw ───────────────────────────────────────────────

    public function throw(): static
    {
        if ($this->failed()) {
            throw new RequestException($this);
        }

        return $this;
    }

    public function throwIf(bool $condition): static
    {
        return $condition ? $this->throw() : $this;
    }

    // ─── Debug ───────────────────────────────────────────────

    public function transferTime(): float
    {
        return (float) ($this->info['total_time'] ?? 0.0);
    }
}