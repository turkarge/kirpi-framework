<?php

declare(strict_types=1);

namespace Core\Queue;

use Core\Database\DatabaseManager;

class QueueManager
{
    private array $resolved = [];

    public function __construct(
        private readonly array           $config,
        private readonly DatabaseManager $db,
    ) {}

    // ─── Driver ──────────────────────────────────────────────

    public function driver(?string $name = null): QueueDriverInterface
    {
        $name ??= $this->config['default'] ?? 'sync';

        if (isset($this->resolved[$name])) {
            return $this->resolved[$name];
        }

        $config = $this->config['connections'][$name]
            ?? throw new \InvalidArgumentException("Queue connection [{$name}] not configured.");

        return $this->resolved[$name] = $this->createDriver($config);
    }

    private function createDriver(array $config): QueueDriverInterface
    {
        return match($config['driver']) {
            'sync'     => new SyncQueueDriver(),
            'database' => new DatabaseQueueDriver($this->db),
            default    => throw new \RuntimeException("Queue driver [{$config['driver']}] not supported."),
        };
    }

    // ─── Proxy Methods ───────────────────────────────────────

    public function push(Job $job, ?string $queue = null): string
    {
        return $this->driver()->push($job, $queue);
    }

    public function later(int $delay, Job $job, ?string $queue = null): string
    {
        return $this->driver()->later($delay, $job, $queue);
    }

    public function pop(?string $queue = null): ?array
    {
        return $this->driver()->pop($queue);
    }

    public function size(?string $queue = null): int
    {
        return $this->driver()->size($queue);
    }

    // ─── Worker ──────────────────────────────────────────────

    public function work(string $queue = 'default', int $sleep = 3, int $tries = 3): void
    {
        while (true) {
            $item = $this->driver()->pop($queue);

            if ($item === null) {
                sleep($sleep);
                continue;
            }

            $this->processJob($item, $tries);
        }
    }

    private function processJob(array $item, int $maxTries): void
    {
        /** @var Job $job */
        $job = $item['job'];

        try {
            $job->incrementAttempts();
            $job->handle();
            $this->driver()->ack($item['id'], $item['queue']);

        } catch (\Throwable $e) {
            if ($job->attempts() >= min($maxTries, $job->tries)) {
                $job->markAsFailed();
                $job->failed($e);
                $this->driver()->fail($item['id'], $e, $item['queue']);

                app(\Core\Logging\Logger::class)->error(
                    "Job failed: " . get_class($job),
                    ['error' => $e->getMessage()]
                );
            } else {
                // Retry — backoff ile tekrar kuyruğa al
                $this->driver()->later($job->backoff, $job, $item['queue']);
                $this->driver()->ack($item['id'], $item['queue']);
            }
        }
    }
}