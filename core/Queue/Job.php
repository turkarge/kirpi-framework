<?php

declare(strict_types=1);

namespace Core\Queue;

abstract class Job
{
    public int    $tries      = 3;
    public int    $timeout    = 60;
    public int    $backoff    = 5;
    public string $queue      = 'default';
    public bool   $deleteWhenMissingModels = true;

    private int   $attempts   = 0;
    private bool  $failed     = false;
    private bool  $released   = false;
    private bool  $deleted    = false;

    abstract public function handle(): void;

    public function failed(\Throwable $e): void
    {
        // Alt sınıflar override edebilir
    }

    public function middleware(): array
    {
        return [];
    }

    // ─── Attempt Yönetimi ────────────────────────────────────

    public function incrementAttempts(): void
    {
        $this->attempts++;
    }

    public function attempts(): int
    {
        return $this->attempts;
    }

    public function hasFailed(): bool
    {
        return $this->failed;
    }

    public function markAsFailed(): void
    {
        $this->failed = true;
    }

    // ─── Release & Delete ────────────────────────────────────

    public function release(int $delay = 0): void
    {
        $this->released = true;
    }

    public function delete(): void
    {
        $this->deleted = true;
    }

    public function isReleased(): bool
    {
        return $this->released;
    }

    public function isDeleted(): bool
    {
        return $this->deleted;
    }

    public function isDeletedOrReleased(): bool
    {
        return $this->deleted || $this->released;
    }
}