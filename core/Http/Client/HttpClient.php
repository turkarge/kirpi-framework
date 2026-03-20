<?php

declare(strict_types=1);

namespace Core\Http\Client;

class HttpClient
{
    // ─── Factory ─────────────────────────────────────────────

    public static function new(): PendingRequest
    {
        return new PendingRequest();
    }

    // ─── Kısa Yollar ─────────────────────────────────────────

    public static function get(string $url, array $query = []): Response
    {
        return static::new()->get($url, $query);
    }

    public static function post(string $url, array $data = []): Response
    {
        return static::new()->post($url, $data);
    }

    public static function put(string $url, array $data = []): Response
    {
        return static::new()->put($url, $data);
    }

    public static function patch(string $url, array $data = []): Response
    {
        return static::new()->patch($url, $data);
    }

    public static function delete(string $url, array $data = []): Response
    {
        return static::new()->delete($url, $data);
    }

    // ─── Pool — Concurrent Requests ──────────────────────────

    public static function pool(\Closure $callback): array
    {
        $requests     = [];
        $multiHandle  = curl_multi_init();
        $handles      = [];
        $responses    = [];

        // Pool nesnesini oluştur
$pool = new class {
    public array $requests = [];

    public function add(string $key, \Closure $request): static
    {
        $this->requests[$key] = $request;
        return $this;
    }
};

        $callback($pool);

        // Tüm request'leri hazırla
        foreach ($pool->requests as $key => $requestFn) {
            $pending  = $requestFn(new PendingRequest());
            $ch       = curl_init();

            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HEADER         => true,
                CURLOPT_TIMEOUT        => 30,
            ]);

            $handles[$key] = $ch;
            curl_multi_add_handle($multiHandle, $ch);
        }

        // Hepsini çalıştır
        do {
            $status = curl_multi_exec($multiHandle, $running);
            curl_multi_select($multiHandle);
        } while ($running > 0);

        // Sonuçları topla
        foreach ($handles as $key => $ch) {
            $raw        = curl_multi_getcontent($ch);
            $info       = curl_getinfo($ch);
            $headerSize = $info['header_size'];

            $responses[$key] = new Response(
                status:  $info['http_code'],
                headers: [],
                body:    substr($raw, $headerSize),
                info:    $info,
            );

            curl_multi_remove_handle($multiHandle, $ch);
            curl_close($ch);
        }

        curl_multi_close($multiHandle);

        return $responses;
    }

    // ─── Fake — Test için ────────────────────────────────────

    private static array $fakeResponses = [];
    private static bool  $faking        = false;

    public static function fake(array $responses = []): void
    {
        static::$faking        = true;
        static::$fakeResponses = $responses;
    }

    public static function isFaking(): bool
    {
        return static::$faking;
    }

    public static function getFakeResponse(string $url): ?Response
    {
        foreach (static::$fakeResponses as $pattern => $data) {
            if (fnmatch($pattern, $url)) {
                return new Response(
                    status:  $data['status']  ?? 200,
                    headers: $data['headers'] ?? [],
                    body:    is_array($data['body'] ?? $data)
                        ? json_encode($data['body'] ?? $data)
                        : ($data['body'] ?? json_encode($data)),
                );
            }
        }

        return null;
    }
}