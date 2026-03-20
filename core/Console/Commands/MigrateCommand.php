<?php

declare(strict_types=1);

namespace Core\Console\Commands;

use Core\Console\Command;
use Core\Migration\Migrator;

class MigrateCommand extends Command
{
    protected string $signature   = 'migrate';
    protected string $description = 'Run pending migrations';

    public function handle(): int
    {
        $migrator = app(Migrator::class);
        $ran      = $migrator->run();

        if (empty($ran)) {
            $this->info('Nothing to migrate.');
            return 0;
        }

        foreach ($ran as $migration) {
            $this->success("Migrated: {$migration}");
        }

        $this->line();
        $this->info(count($ran) . ' migration(s) ran successfully.');

        return 0;
    }
}