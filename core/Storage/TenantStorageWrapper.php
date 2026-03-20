<?php

declare(strict_types=1);

namespace Core\Storage;

class TenantStorageWrapper implements StorageDriverInterface
{
    public function __construct(
        private readonly StorageDriverInterface $disk,
        private readonly string                 $prefix,
    ) {}

    private function prefixed(string $path): string
    {
        return rtrim($this->prefix, '/') . '/' . ltrim($path, '/');
    }

    public function exists(string $path): bool
    {
        return $this->disk->exists($this->prefixed($path));
    }

    public function get(string $path): string
    {
        return $this->disk->get($this->prefixed($path));
    }

    public function put(string $path, string $contents, array $options = []): bool
    {
        return $this->disk->put($this->prefixed($path), $contents, $options);
    }

    public function putFile(string $path, string $localPath, array $options = []): bool
    {
        return $this->disk->putFile($this->prefixed($path), $localPath, $options);
    }

    public function delete(string|array $paths): bool
    {
        $prefixed = array_map(
            fn($p) => $this->prefixed($p),
            (array) $paths
        );

        return $this->disk->delete($prefixed);
    }

    public function move(string $from, string $to): bool
    {
        return $this->disk->move($this->prefixed($from), $this->prefixed($to));
    }

    public function copy(string $from, string $to): bool
    {
        return $this->disk->copy($this->prefixed($from), $this->prefixed($to));
    }

    public function size(string $path): int
    {
        return $this->disk->size($this->prefixed($path));
    }

    public function lastModified(string $path): int
    {
        return $this->disk->lastModified($this->prefixed($path));
    }

    public function mimeType(string $path): string
    {
        return $this->disk->mimeType($this->prefixed($path));
    }

    public function files(string $directory = ''): array
    {
        return $this->disk->files($this->prefixed($directory));
    }

    public function directories(string $directory = ''): array
    {
        return $this->disk->directories($this->prefixed($directory));
    }

    public function makeDirectory(string $path): bool
    {
        return $this->disk->makeDirectory($this->prefixed($path));
    }

    public function deleteDirectory(string $path): bool
    {
        return $this->disk->deleteDirectory($this->prefixed($path));
    }

    public function url(string $path): string
    {
        return $this->disk->url($this->prefixed($path));
    }

    public function temporaryUrl(string $path, int $expiresIn = 3600): string
    {
        return $this->disk->temporaryUrl($this->prefixed($path), $expiresIn);
    }

    public function visibility(string $path): string
    {
        return $this->disk->visibility($this->prefixed($path));
    }

    public function setVisibility(string $path, string $visibility): bool
    {
        return $this->disk->setVisibility($this->prefixed($path), $visibility);
    }

    public function stream(string $path): mixed
    {
        return $this->disk->stream($this->prefixed($path));
    }

    public function putStream(string $path, mixed $stream): bool
    {
        return $this->disk->putStream($this->prefixed($path), $stream);
    }
}