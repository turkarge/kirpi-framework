<?php

declare(strict_types=1);

namespace Core\Console\Commands;

use Core\Console\Command;
use Core\Queue\QueueManager;

class QueueWorkCommand extends Command
{
    protected string $signature   = 'queue:work';
    protected string $description = 'Start processing jobs on the queue';

    public function handle(): int
    {
        $queue = $this->option('queue', 'default');
        $sleep = (int) $this->option('sleep', 3);
        $tries = (int) $this->option('tries', 3);

        $this->info("🦔 Queue worker started.");
        $this->line("Queue : {$queue}");
        $this->line("Sleep : {$sleep}s");
        $this->line("Tries : {$tries}");
        $this->line(str_repeat('-', 40));

        $manager = app(QueueManager::class);

        while (true) {
            $item = $manager->driver()->pop($queue);

            if ($item === null) {
                sleep($sleep);
                continue;
            }

            $job = $item['job'];
            $this->comment("Processing: " . get_class($job));

            try {
                $job->incrementAttempts();
                $job->handle();
                $manager->driver()->ack($item['id'], $queue);
                $this->success("Processed: " . get_class($job));

            } catch (\Throwable $e) {
                if ($job->attempts() >= min($tries, $job->tries)) {
                    $job->markAsFailed();
                    $job->failed($e);
                    $manager->driver()->fail($item['id'], $e, $queue);
                    $this->error("Failed: " . get_class($job) . " — " . $e->getMessage());
                } else {
                    $manager->driver()->later($job->backoff, $job, $queue);
                    $manager->driver()->ack($item['id'], $queue);
                    $this->warning("Retrying: " . get_class($job));
                }
            }
        }
    }
}