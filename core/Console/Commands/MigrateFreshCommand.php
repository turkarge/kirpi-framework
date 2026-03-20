<?php

declare(strict_types=1);

namespace Core\Console\Commands;

use Core\Console\Command;
use Core\Migration\Migrator;

class MigrateFreshCommand extends Command
{
    protected string $signature   = 'migrate:fresh';
    protected string $description = 'Drop all tables and re-run all migrations';

    public function handle(): int
    {
        if (!$this->option('force')) {
            $confirmed = $this->confirm(
                '⚠️  All tables will be dropped. Are you sure?',
                false
            );

            if (!$confirmed) {
                $this->warning('Operation cancelled.');
                return 0;
            }
        }

        $migrator = app(Migrator::class);
        $migrator->fresh();

        $this->success('Database refreshed successfully.');

        return 0;
    }
}