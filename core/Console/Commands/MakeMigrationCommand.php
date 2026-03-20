<?php

declare(strict_types=1);

namespace Core\Console\Commands;

use Core\Console\Command;

class MakeMigrationCommand extends Command
{
    protected string $signature   = 'make:migration {name}';
    protected string $description = 'Create a new migration file';

    public function handle(): int
    {
        $name = $this->argument('name');

        if (!$name) {
            $this->error('Migration name is required.');
            $this->line('Usage: php framework make:migration create_users_table');
            return 1;
        }

        $timestamp = date('Y_m_d_His');
        $filename  = "{$timestamp}_{$name}.php";
        $path      = BASE_PATH . "/database/migrations/{$filename}";

        $isCreate = str_starts_with($name, 'create_');
        $table    = $isCreate
            ? str_replace(['create_', '_table'], '', $name)
            : 'table_name';

        $stubFile = $isCreate ? 'create.stub' : 'alter.stub';
        $stubPath = BASE_PATH . "/core/Migration/Stubs/{$stubFile}";

        if (!file_exists($stubPath)) {
            $this->error("Stub file not found: {$stubPath}");
            return 1;
        }

        $stub    = file_get_contents($stubPath);
        $content = str_replace('{{table}}', $table, $stub);

        file_put_contents($path, $content);

        $this->success("Created: database/migrations/{$filename}");

        return 0;
    }
}