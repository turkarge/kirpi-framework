<?php

declare(strict_types=1);

namespace Core\Queue;

class SyncQueueDriver implements QueueDriverInterface
{
    public function push(Job $job, ?string $queue = null): string
    {
        $id = bin2hex(random_bytes(8));

        try {
            $job->incrementAttempts();
            $job->handle();
        } catch (\Throwable $e) {
            $job->markAsFailed();
            $job->failed($e);
            throw $e;
        }

        return $id;
    }

    public function later(int $delay, Job $job, ?string $queue = null): string
    {
        // Sync driver delay'i yok sayar
        return $this->push($job, $queue);
    }

    public function pop(?string $queue = null): ?array
    {
        return null;
    }

    public function ack(string $id, ?string $queue = null): void {}

    public function fail(string $id, \Throwable $e, ?string $queue = null): void {}

    public function size(?string $queue = null): int
    {
        return 0;
    }

    public function clear(?string $queue = null): void {}
}