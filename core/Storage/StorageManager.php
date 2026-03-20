<?php

declare(strict_types=1);

namespace Core\Storage;

use Core\Storage\Drivers\LocalDriver;
use Core\Storage\Drivers\S3Driver;

class StorageManager
{
    private array $resolved = [];

    public function __construct(private readonly array $config) {}

    // ─── Disk Seçimi ─────────────────────────────────────────

    public function disk(?string $name = null): StorageDriverInterface
    {
        $name ??= $this->config['default'] ?? 'local';

        if (isset($this->resolved[$name])) {
            return $this->resolved[$name];
        }

        $config = $this->config['disks'][$name]
            ?? throw new \InvalidArgumentException("Storage disk [{$name}] not configured.");

        return $this->resolved[$name] = $this->createDriver($config);
    }

    private function createDriver(array $config): StorageDriverInterface
    {
        return match($config['driver']) {
            'local' => new LocalDriver($config),
            's3'    => new S3Driver($config),
            default => throw new \RuntimeException("Storage driver [{$config['driver']}] not supported."),
        };
    }

    // ─── Tenant Disk ─────────────────────────────────────────

    public function tenantDisk(int|string $tenantId, ?string $disk = null): StorageDriverInterface
    {
        return new TenantStorageWrapper(
            $this->disk($disk),
            "tenants/{$tenantId}"
        );
    }

    // ─── Proxy Methods ───────────────────────────────────────

    public function exists(string $path): bool           { return $this->disk()->exists($path); }
    public function get(string $path): string            { return $this->disk()->get($path); }
    public function put(string $path, string $contents, array $options = []): bool { return $this->disk()->put($path, $contents, $options); }
    public function delete(string|array $paths): bool    { return $this->disk()->delete($paths); }
    public function move(string $from, string $to): bool { return $this->disk()->move($from, $to); }
    public function copy(string $from, string $to): bool { return $this->disk()->copy($from, $to); }
    public function url(string $path): string            { return $this->disk()->url($path); }
    public function files(string $dir = ''): array       { return $this->disk()->files($dir); }
    public function makeDirectory(string $path): bool    { return $this->disk()->makeDirectory($path); }
    public function temporaryUrl(string $path, int $exp = 3600): string { return $this->disk()->temporaryUrl($path, $exp); }
}