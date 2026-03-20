<?php

declare(strict_types=1);

namespace Core\Storage\Drivers;

use Core\Storage\StorageDriverInterface;

class S3Driver implements StorageDriverInterface
{
    private string $bucket;
    private string $region;
    private string $key;
    private string $secret;
    private string $endpoint;
    private string $cdnUrl;

    public function __construct(array $config)
    {
        $this->bucket   = $config['bucket']   ?? '';
        $this->region   = $config['region']   ?? 'us-east-1';
        $this->key      = $config['key']      ?? '';
        $this->secret   = $config['secret']   ?? '';
        $this->endpoint = rtrim($config['endpoint'] ?? "https://s3.{$this->region}.amazonaws.com", '/');
        $this->cdnUrl   = rtrim($config['cdn_url']  ?? '', '/');
    }

    public function exists(string $path): bool
    {
        $response = $this->request('HEAD', $path);
        return $response['status'] === 200;
    }

    public function get(string $path): string
    {
        $response = $this->request('GET', $path);

        if ($response['status'] !== 200) {
            throw new \RuntimeException("S3: Failed to get [{$path}]");
        }

        return $response['body'];
    }

    public function put(string $path, string $contents, array $options = []): bool
    {
        $headers = [
            'Content-Type' => $options['mime'] ?? 'application/octet-stream',
            'x-amz-acl'   => ($options['visibility'] ?? 'private') === 'public' ? 'public-read' : 'private',
        ];

        $response = $this->request('PUT', $path, $contents, $headers);
        return $response['status'] === 200;
    }

    public function putFile(string $path, string $localPath, array $options = []): bool
    {
        $contents = file_get_contents($localPath);
        $mime     = mime_content_type($localPath) ?: 'application/octet-stream';

        return $this->put($path, $contents, array_merge($options, ['mime' => $mime]));
    }

    public function delete(string|array $paths): bool
    {
        foreach ((array) $paths as $path) {
            $this->request('DELETE', $path);
        }

        return true;
    }

    public function move(string $from, string $to): bool
    {
        $this->copy($from, $to);
        return $this->delete($from);
    }

    public function copy(string $from, string $to): bool
    {
        $headers = [
            'x-amz-copy-source' => "/{$this->bucket}/" . ltrim($from, '/'),
        ];

        $response = $this->request('PUT', $to, '', $headers);
        return $response['status'] === 200;
    }

    public function size(string $path): int
    {
        $response = $this->request('HEAD', $path);
        return (int) ($response['headers']['Content-Length'] ?? 0);
    }

    public function lastModified(string $path): int
    {
        $response = $this->request('HEAD', $path);
        $lastMod  = $response['headers']['Last-Modified'] ?? null;
        return $lastMod ? strtotime($lastMod) : 0;
    }

    public function mimeType(string $path): string
    {
        $response = $this->request('HEAD', $path);
        return $response['headers']['Content-Type'] ?? 'application/octet-stream';
    }

    public function files(string $directory = ''): array
    {
        $prefix   = ltrim($directory, '/');
        $response = $this->request('GET', '', '', [], ['prefix' => $prefix]);

        preg_match_all('/<Key>(.*?)<\/Key>/', $response['body'], $matches);
        return $matches[1] ?? [];
    }

    public function directories(string $directory = ''): array
    {
        return [];
    }

    public function makeDirectory(string $path): bool
    {
        return true; // S3'te klasör kavramı yok
    }

    public function deleteDirectory(string $path): bool
    {
        $files = $this->files($path);

        foreach ($files as $file) {
            $this->delete($file);
        }

        return true;
    }

    public function url(string $path): string
    {
        if ($this->cdnUrl) {
            return $this->cdnUrl . '/' . ltrim($path, '/');
        }

        return "{$this->endpoint}/{$this->bucket}/" . ltrim($path, '/');
    }

    public function temporaryUrl(string $path, int $expiresIn = 3600): string
    {
        $expires    = time() + $expiresIn;
        $datetime   = gmdate('Ymd\THis\Z');
        $date       = gmdate('Ymd');
        $credential = "{$this->key}/{$date}/{$this->region}/s3/aws4_request";

        $queryParams = http_build_query([
            'X-Amz-Algorithm'     => 'AWS4-HMAC-SHA256',
            'X-Amz-Credential'    => $credential,
            'X-Amz-Date'          => $datetime,
            'X-Amz-Expires'       => $expiresIn,
            'X-Amz-SignedHeaders' => 'host',
        ]);

        $canonicalRequest = implode("\n", [
            'GET',
            '/' . ltrim($path, '/'),
            $queryParams,
            'host:' . parse_url($this->endpoint, PHP_URL_HOST) . "\n",
            'host',
            'UNSIGNED-PAYLOAD',
        ]);

        $stringToSign = implode("\n", [
            'AWS4-HMAC-SHA256',
            $datetime,
            "{$date}/{$this->region}/s3/aws4_request",
            hash('sha256', $canonicalRequest),
        ]);

        $signature = $this->calculateSignature($stringToSign, $date);

        return $this->url($path) . "?{$queryParams}&X-Amz-Signature={$signature}";
    }

    public function visibility(string $path): string
    {
        $response = $this->request('GET', $path . '?acl');
        return str_contains($response['body'], 'public-read') ? 'public' : 'private';
    }

    public function setVisibility(string $path, string $visibility): bool
    {
        $headers = [
            'x-amz-acl' => $visibility === 'public' ? 'public-read' : 'private',
        ];

        $response = $this->request('PUT', $path . '?acl', '', $headers);
        return $response['status'] === 200;
    }

    public function stream(string $path): mixed
    {
        $url = $this->url($path);
        return fopen($url, 'rb');
    }

    public function putStream(string $path, mixed $stream): bool
    {
        $contents = stream_get_contents($stream);
        return $this->put($path, $contents);
    }

    // ─── AWS Signature v4 ────────────────────────────────────

    private function request(
        string $method,
        string $path,
        string $body    = '',
        array  $headers = [],
        array  $query   = [],
    ): array {
        $datetime = gmdate('Ymd\THis\Z');
        $date     = gmdate('Ymd');

        $url = "{$this->endpoint}/{$this->bucket}/" . ltrim($path, '/');

        if (!empty($query)) {
            $url .= '?' . http_build_query($query);
        }

        $host = parse_url($this->endpoint, PHP_URL_HOST);

        $headers = array_merge($headers, [
            'Host'                  => $host,
            'X-Amz-Date'           => $datetime,
            'X-Amz-Content-Sha256' => hash('sha256', $body),
        ]);

        $signedHeaders = implode(';', array_map('strtolower', array_keys($headers)));

        $canonicalHeaders = implode("\n", array_map(
            fn($k, $v) => strtolower($k) . ':' . trim($v),
            array_keys($headers),
            array_values($headers)
        )) . "\n";

        $canonicalRequest = implode("\n", [
            $method,
            '/' . $this->bucket . '/' . ltrim($path, '/'),
            http_build_query($query),
            $canonicalHeaders,
            $signedHeaders,
            hash('sha256', $body),
        ]);

        $stringToSign = implode("\n", [
            'AWS4-HMAC-SHA256',
            $datetime,
            "{$date}/{$this->region}/s3/aws4_request",
            hash('sha256', $canonicalRequest),
        ]);

        $signature = $this->calculateSignature($stringToSign, $date);

        $headers['Authorization'] = "AWS4-HMAC-SHA256 Credential={$this->key}/{$date}/{$this->region}/s3/aws4_request, SignedHeaders={$signedHeaders}, Signature={$signature}";

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => true,
            CURLOPT_POSTFIELDS     => $body ?: null,
            CURLOPT_HTTPHEADER     => array_map(
                fn($k, $v) => "{$k}: {$v}",
                array_keys($headers),
                array_values($headers)
            ),
        ]);

        $raw        = curl_exec($ch);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $status     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $rawHeaders = substr($raw, 0, $headerSize);
        $body       = substr($raw, $headerSize);

        $parsedHeaders = [];
        foreach (explode("\r\n", $rawHeaders) as $line) {
            if (str_contains($line, ':')) {
                [$k, $v] = explode(':', $line, 2);
                $parsedHeaders[trim($k)] = trim($v);
            }
        }

        return [
            'status'  => $status,
            'headers' => $parsedHeaders,
            'body'    => $body,
        ];
    }

    private function calculateSignature(string $stringToSign, string $date): string
    {
        $dateKey    = hash_hmac('sha256', $date,           'AWS4' . $this->secret, true);
        $regionKey  = hash_hmac('sha256', $this->region,   $dateKey,               true);
        $serviceKey = hash_hmac('sha256', 's3',            $regionKey,             true);
        $signingKey = hash_hmac('sha256', 'aws4_request',  $serviceKey,            true);

        return hash_hmac('sha256', $stringToSign, $signingKey);
    }
}