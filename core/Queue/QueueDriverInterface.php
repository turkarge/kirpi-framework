<?php

declare(strict_types=1);

namespace Core\Queue;

interface QueueDriverInterface
{
    public function push(Job $job, ?string $queue = null): string;
    public function later(int $delay, Job $job, ?string $queue = null): string;
    public function pop(?string $queue = null): ?array;
    public function ack(string $id, ?string $queue = null): void;
    public function fail(string $id, \Throwable $e, ?string $queue = null): void;
    public function size(?string $queue = null): int;
    public function clear(?string $queue = null): void;
}