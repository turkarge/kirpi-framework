<?php

declare(strict_types=1);

namespace Core\Storage;

interface StorageDriverInterface
{
    public function exists(string $path): bool;
    public function get(string $path): string;
    public function put(string $path, string $contents, array $options = []): bool;
    public function putFile(string $path, string $localPath, array $options = []): bool;
    public function delete(string|array $paths): bool;
    public function move(string $from, string $to): bool;
    public function copy(string $from, string $to): bool;
    public function size(string $path): int;
    public function lastModified(string $path): int;
    public function mimeType(string $path): string;
    public function files(string $directory = ''): array;
    public function directories(string $directory = ''): array;
    public function makeDirectory(string $path): bool;
    public function deleteDirectory(string $path): bool;
    public function url(string $path): string;
    public function temporaryUrl(string $path, int $expiresIn = 3600): string;
    public function visibility(string $path): string;
    public function setVisibility(string $path, string $visibility): bool;
    public function stream(string $path): mixed;
    public function putStream(string $path, mixed $stream): bool;
}