<?php

declare(strict_types=1);

namespace Core\Http\Client;

class PendingRequest
{
    private array   $headers      = [];
    private ?string $baseUrl      = null;
    private string  $bodyFormat   = 'json';
    private int     $timeout      = 30;
    private int     $connectTimeout = 10;
    private int     $retryTimes   = 0;
    private int     $retryDelay   = 100;
    private array   $retryWhen    = [];
    private bool    $verifySsl    = true;
    private ?string $proxy        = null;
    private ?array  $basicAuth    = null;
    private array   $options      = [];

    // ─── Auth ────────────────────────────────────────────────

    public function withToken(string $token, string $type = 'Bearer'): static
    {
        return $this->withHeaders(['Authorization' => "{$type} {$token}"]);
    }

    public function withBasicAuth(string $username, string $password): static
    {
        $this->basicAuth = [$username, $password];
        return $this;
    }

    // ─── Headers ─────────────────────────────────────────────

    public function withHeaders(array $headers): static
    {
        $this->headers = array_merge($this->headers, $headers);
        return $this;
    }

    public function accept(string $contentType): static
    {
        return $this->withHeaders(['Accept' => $contentType]);
    }

    public function acceptJson(): static
    {
        return $this->accept('application/json');
    }

    public function contentType(string $contentType): static
    {
        return $this->withHeaders(['Content-Type' => $contentType]);
    }

    public function withRequestId(): static
    {
        return $this->withHeaders(['X-Request-ID' => bin2hex(random_bytes(8))]);
    }

    // ─── Body Format ─────────────────────────────────────────

    public function asJson(): static
    {
        $this->bodyFormat = 'json';
        return $this->contentType('application/json');
    }

    public function asForm(): static
    {
        $this->bodyFormat = 'form';
        return $this->contentType('application/x-www-form-urlencoded');
    }

    public function asMultipart(): static
    {
        $this->bodyFormat = 'multipart';
        return $this;
    }

    // ─── Timeout & Retry ─────────────────────────────────────

    public function timeout(int $seconds): static
    {
        $this->timeout = $seconds;
        return $this;
    }

    public function connectTimeout(int $seconds): static
    {
        $this->connectTimeout = $seconds;
        return $this;
    }

    public function retry(int $times, int $sleepMs = 100, ?\Closure $when = null): static
    {
        $this->retryTimes = $times;
        $this->retryDelay = $sleepMs;

        if ($when) {
            $this->retryWhen[] = $when;
        }

        return $this;
    }

    public function retryOnServerError(): static
    {
        return $this->retry(3, 500, fn(Response $r) => $r->serverError());
    }

    // ─── SSL & Proxy ─────────────────────────────────────────

    public function withoutVerifying(): static
    {
        $this->verifySsl = false;
        return $this;
    }

    public function withProxy(string $proxy): static
    {
        $this->proxy = $proxy;
        return $this;
    }

    // ─── Base URL ────────────────────────────────────────────

    public function baseUrl(string $url): static
    {
        $this->baseUrl = rtrim($url, '/');
        return $this;
    }

    // ─── HTTP Methods ────────────────────────────────────────

    public function get(string $url, array $query = []): Response
    {
        if (!empty($query)) {
            $url .= '?' . http_build_query($query);
        }

        return $this->send('GET', $url);
    }

    public function post(string $url, array $data = []): Response
    {
        return $this->send('POST', $url, $data);
    }

    public function put(string $url, array $data = []): Response
    {
        return $this->send('PUT', $url, $data);
    }

    public function patch(string $url, array $data = []): Response
    {
        return $this->send('PATCH', $url, $data);
    }

    public function delete(string $url, array $data = []): Response
    {
        return $this->send('DELETE', $url, $data);
    }

    public function head(string $url): Response
    {
        return $this->send('HEAD', $url);
    }

    // ─── Send ────────────────────────────────────────────────

    public function send(string $method, string $url, array $data = []): Response
    {
        $url      = $this->buildUrl($url);
        $attempt  = 0;
        $response = null;

        do {
            if ($attempt > 0) {
                usleep($this->retryDelay * 1000);
            }

            try {
                $response = $this->sendRequest($method, $url, $data);
            } catch (\RuntimeException $e) {
                if ($attempt >= $this->retryTimes) throw $e;
            }

            $attempt++;

        } while (
            $attempt <= $this->retryTimes &&
            $response !== null &&
            $this->shouldRetry($response)
        );

        return $response ?? throw new \RuntimeException("Request failed after {$this->retryTimes} retries.");
    }

    private function sendRequest(string $method, string $url, array $data): Response
    {
        $ch   = curl_init();
        $body = $this->prepareBody($data);

        $curlOptions = [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => true,
            CURLOPT_TIMEOUT        => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => $this->connectTimeout,
            CURLOPT_SSL_VERIFYPEER => $this->verifySsl,
            CURLOPT_SSL_VERIFYHOST => $this->verifySsl ? 2 : 0,
            CURLOPT_CUSTOMREQUEST  => $method,
        ];

        $headers = $this->buildHeaders();
        if (!empty($headers)) {
            $curlOptions[CURLOPT_HTTPHEADER] = $headers;
        }

        if ($this->basicAuth !== null) {
            $curlOptions[CURLOPT_USERPWD] = implode(':', $this->basicAuth);
        }

        if (!empty($body)) {
            $curlOptions[CURLOPT_POSTFIELDS] = $body;
        }

        if ($this->proxy !== null) {
            $curlOptions[CURLOPT_PROXY] = $this->proxy;
        }

        curl_setopt_array($ch, $curlOptions);

if (!empty($this->options)) {
    curl_setopt_array($ch, $this->options);
}

        $raw    = curl_exec($ch);
        $errno  = curl_errno($ch);
        $info   = curl_getinfo($ch);

        if ($errno !== 0) {
            throw new \RuntimeException("cURL error: " . curl_strerror($errno));
        }

        $headerSize = $info['header_size'];
        $rawHeaders = substr($raw, 0, $headerSize);
        $rawBody    = substr($raw, $headerSize);

        return new Response(
            status:  $info['http_code'],
            headers: $this->parseHeaders($rawHeaders),
            body:    $rawBody,
            info:    $info,
        );
    }

    // ─── Helpers ─────────────────────────────────────────────

    private function prepareBody(array $data): string|array|null
    {
        if (empty($data)) return null;

        return match($this->bodyFormat) {
            'json'      => json_encode($data),
            'form'      => http_build_query($data),
            'multipart' => $data,
            default     => json_encode($data),
        };
    }

    private function buildUrl(string $url): string
    {
        if ($this->baseUrl && !str_starts_with($url, 'http')) {
            return $this->baseUrl . '/' . ltrim($url, '/');
        }

        return $url;
    }

    private function buildHeaders(): array
    {
        return array_map(
            fn($key, $value) => "{$key}: {$value}",
            array_keys($this->headers),
            array_values($this->headers)
        );
    }

    private function parseHeaders(string $raw): array
    {
        $headers = [];

        foreach (explode("\r\n", $raw) as $line) {
            if (str_contains($line, ':')) {
                [$key, $value] = explode(':', $line, 2);
                $headers[trim($key)] = trim($value);
            }
        }

        return $headers;
    }

    private function shouldRetry(Response $response): bool
    {
        foreach ($this->retryWhen as $condition) {
            if ($condition($response)) return true;
        }

        return false;
    }
}