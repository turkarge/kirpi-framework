<?php

declare(strict_types=1);

namespace Core\Console\Commands;

use Core\Console\Command;
use Core\Migration\Migrator;

class MigrateStatusCommand extends Command
{
    protected string $signature   = 'migrate:status';
    protected string $description = 'Show the status of each migration';

    public function handle(): int
    {
        $migrator = app(Migrator::class);
        $status   = $migrator->status();

        if (empty($status)) {
            $this->info('No migrations found.');
            return 0;
        }

        $this->table(
            ['Migration', 'Status', 'Batch'],
            array_map(fn($row) => [
                $row['migration'],
                $row['ran'] ? '✓ Ran' : '○ Pending',
                $row['batch'] ?? '-',
            ], $status)
        );

        return 0;
    }
}