<?php

declare(strict_types=1);

namespace Core\Console\Commands;

use Core\Console\Command;
use Core\Cache\CacheManager;

class CacheClearCommand extends Command
{
    protected string $signature   = 'cache:clear';
    protected string $description = 'Flush the application cache';

    public function handle(): int
    {
        $driver = $this->option('driver');

        $cache = app(CacheManager::class);

        try {
            if ($driver) {
                $cache->driver($driver)->flush();
                $this->success("Cache [{$driver}] cleared successfully.");
            } else {
                $cache->flush();
                $this->success('Application cache cleared successfully.');
            }

            return 0;

        } catch (\Throwable $e) {
            $this->error("Failed to clear cache: {$e->getMessage()}");
            return 1;
        }
    }
}