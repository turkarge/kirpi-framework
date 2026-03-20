<?php

declare(strict_types=1);

namespace Core\Storage\Drivers;

use Core\Storage\StorageDriverInterface;

class LocalDriver implements StorageDriverInterface
{
    private string $root;
    private string $publicUrl;

    public function __construct(array $config)
    {
        $this->root      = rtrim($config['root'] ?? storage_path('app'), '/');
        $this->publicUrl = rtrim($config['url']  ?? '', '/');

        if (!is_dir($this->root)) {
            mkdir($this->root, 0755, true);
        }
    }

    public function exists(string $path): bool
    {
        return file_exists($this->fullPath($path));
    }

    public function get(string $path): string
    {
        if (!$this->exists($path)) {
            throw new \RuntimeException("File not found: {$path}");
        }

        return file_get_contents($this->fullPath($path));
    }

    public function put(string $path, string $contents, array $options = []): bool
    {
        $fullPath = $this->fullPath($path);
        $this->ensureDirectory(dirname($fullPath));

        $result = file_put_contents($fullPath, $contents, LOCK_EX) !== false;

        if ($result && isset($options['visibility'])) {
            $this->setVisibility($path, $options['visibility']);
        }

        return $result;
    }

    public function putFile(string $path, string $localPath, array $options = []): bool
    {
        $fullPath = $this->fullPath($path);
        $this->ensureDirectory(dirname($fullPath));

        return copy($localPath, $fullPath);
    }

    public function delete(string|array $paths): bool
    {
        foreach ((array) $paths as $path) {
            if ($this->exists($path)) {
                unlink($this->fullPath($path));
            }
        }

        return true;
    }

    public function move(string $from, string $to): bool
    {
        $this->ensureDirectory(dirname($this->fullPath($to)));
        return rename($this->fullPath($from), $this->fullPath($to));
    }

    public function copy(string $from, string $to): bool
    {
        $this->ensureDirectory(dirname($this->fullPath($to)));
        return copy($this->fullPath($from), $this->fullPath($to));
    }

    public function size(string $path): int
    {
        return filesize($this->fullPath($path)) ?: 0;
    }

    public function lastModified(string $path): int
    {
        return filemtime($this->fullPath($path)) ?: 0;
    }

    public function mimeType(string $path): string
    {
        return mime_content_type($this->fullPath($path)) ?: 'application/octet-stream';
    }

    public function files(string $directory = ''): array
    {
        $path  = $this->fullPath($directory);
        $files = glob($path . '/*');

        return array_values(array_filter($files ?: [], 'is_file'));
    }

    public function directories(string $directory = ''): array
    {
        $path = $this->fullPath($directory);
        $dirs = glob($path . '/*', GLOB_ONLYDIR);

        return $dirs ?: [];
    }

    public function makeDirectory(string $path): bool
    {
        return mkdir($this->fullPath($path), 0755, true);
    }

    public function deleteDirectory(string $path): bool
    {
        $fullPath = $this->fullPath($path);

        if (!is_dir($fullPath)) return false;

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($fullPath, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $file) {
            $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
        }

        return rmdir($fullPath);
    }

    public function url(string $path): string
    {
        return $this->publicUrl . '/' . ltrim($path, '/');
    }

    public function temporaryUrl(string $path, int $expiresIn = 3600): string
    {
        $expires   = time() + $expiresIn;
        $signature = hash_hmac('sha256', $path . $expires, env('APP_KEY', ''));

        return $this->url($path) . "?expires={$expires}&signature={$signature}";
    }

    public function visibility(string $path): string
    {
        $perms = fileperms($this->fullPath($path));
        return ($perms & 0x0004) ? 'public' : 'private';
    }

    public function setVisibility(string $path, string $visibility): bool
    {
        $mode = $visibility === 'public' ? 0644 : 0600;
        return chmod($this->fullPath($path), $mode);
    }

    public function stream(string $path): mixed
    {
        return fopen($this->fullPath($path), 'rb');
    }

    public function putStream(string $path, mixed $stream): bool
    {
        $fullPath = $this->fullPath($path);
        $this->ensureDirectory(dirname($fullPath));

        $dest   = fopen($fullPath, 'wb');
        $result = stream_copy_to_stream($stream, $dest);
        fclose($dest);

        return $result !== false;
    }

    // ─── Private ─────────────────────────────────────────────

    private function fullPath(string $path): string
    {
        return $this->root . '/' . ltrim($path, '/');
    }

    private function ensureDirectory(string $path): void
    {
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
    }
}