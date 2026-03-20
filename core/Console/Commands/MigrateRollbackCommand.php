<?php

declare(strict_types=1);

namespace Core\Console\Commands;

use Core\Console\Command;
use Core\Migration\Migrator;

class MigrateRollbackCommand extends Command
{
    protected string $signature   = 'migrate:rollback';
    protected string $description = 'Rollback the last database migration batch';

    public function handle(): int
    {
        $steps      = (int) $this->option('step', 1);
        $migrator   = app(Migrator::class);
        $rolledBack = $migrator->rollback($steps);

        if (empty($rolledBack)) {
            $this->info('Nothing to rollback.');
            return 0;
        }

        foreach ($rolledBack as $migration) {
            $this->success("Rolled back: {$migration}");
        }

        $this->line();
        $this->info(count($rolledBack) . ' migration(s) rolled back.');

        return 0;
    }
}