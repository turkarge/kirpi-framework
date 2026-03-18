<?php

declare(strict_types=1);

namespace Core\Http;

class UploadedFile
{
    public function __construct(private readonly array $file) {}

    public function name(): string      { return $this->file['name']; }
    public function type(): string      { return $this->file['type']; }
    public function size(): int         { return $this->file['size']; }
    public function tempPath(): string  { return $this->file['tmp_name']; }
    public function error(): int        { return $this->file['error']; }
    public function isValid(): bool     { return $this->file['error'] === UPLOAD_ERR_OK; }

    public function extension(): string
    {
        return strtolower(pathinfo($this->file['name'], PATHINFO_EXTENSION));
    }

    public function mimeType(): string
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $this->file['tmp_name']);
        finfo_close($finfo);
        return $mime;
    }

    public function moveTo(string $destination): bool
    {
        return move_uploaded_file($this->file['tmp_name'], $destination);
    }

    public function getContents(): string
    {
        return file_get_contents($this->file['tmp_name']);
    }
}