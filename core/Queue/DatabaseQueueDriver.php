<?php

declare(strict_types=1);

namespace Core\Queue;

use Core\Database\DatabaseManager;

class DatabaseQueueDriver implements QueueDriverInterface
{
    private string $table = 'jobs';

    public function __construct(
        private readonly DatabaseManager $db,
    ) {}

    public function push(Job $job, ?string $queue = null): string
    {
        $queue = $queue ?? $job->queue;
        $id    = bin2hex(random_bytes(16));

        $this->db->table($this->table)->insert([
            'id'           => $id,
            'queue'        => $queue,
            'payload'      => serialize($job),
            'attempts'     => 0,
            'available_at' => time(),
            'created_at'   => time(),
        ]);

        return $id;
    }

    public function later(int $delay, Job $job, ?string $queue = null): string
    {
        $queue = $queue ?? $job->queue;
        $id    = bin2hex(random_bytes(16));

        $this->db->table($this->table)->insert([
            'id'           => $id,
            'queue'        => $queue,
            'payload'      => serialize($job),
            'attempts'     => 0,
            'available_at' => time() + $delay,
            'created_at'   => time(),
        ]);

        return $id;
    }

    public function pop(?string $queue = null): ?array
    {
        $queue ??= 'default';

        $job = $this->db->table($this->table)
            ->where('queue', $queue)
            ->where('available_at', '<=', time())
            ->whereNull('reserved_at')
            ->orderBy('available_at')
            ->first();

        if (!$job) return null;

        // Reserve et
        $this->db->table($this->table)
            ->where('id', $job->id)
            ->update([
                'reserved_at' => time(),
                'attempts'    => $job->attempts + 1,
            ]);

        return [
            'id'      => $job->id,
            'queue'   => $job->queue,
            'job'     => unserialize($job->payload),
            'attempts'=> (int) $job->attempts + 1,
        ];
    }

    public function ack(string $id, ?string $queue = null): void
    {
        $this->db->table($this->table)
            ->where('id', $id)
            ->delete();
    }

    public function fail(string $id, \Throwable $e, ?string $queue = null): void
    {
        $job = $this->db->table($this->table)
            ->where('id', $id)
            ->first();

        if (!$job) return;

        // Failed jobs tablosuna taşı
        $this->db->table('failed_jobs')->insert([
            'id'         => $id,
            'queue'      => $job->queue,
            'payload'    => $job->payload,
            'exception'  => $e->getMessage() . "\n" . $e->getTraceAsString(),
            'failed_at'  => time(),
        ]);

        $this->db->table($this->table)
            ->where('id', $id)
            ->delete();
    }

    public function size(?string $queue = null): int
    {
        $queue ??= 'default';

        return $this->db->table($this->table)
            ->where('queue', $queue)
            ->count();
    }

    public function clear(?string $queue = null): void
    {
        $query = $this->db->table($this->table);

        if ($queue !== null) {
            $query->where('queue', $queue);
        }

        $query->delete();
    }
}