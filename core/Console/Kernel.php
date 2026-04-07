<?php

declare(strict_types=1);

namespace Core\Console;

use Core\Container\Container;

class Kernel
{
    private array $commands = [];

    public function __construct(
        private readonly Container $app,
    ) {
        $this->registerCoreCommands();
    }

    // ─── Komut Kayıt ─────────────────────────────────────────

    public function register(string $command): void
    {
        $instance = new $command();
        $this->commands[$instance->getSignature()] = $command;
    }

    public function registerMany(array $commands): void
    {
        foreach ($commands as $command) {
            $this->register($command);
        }
    }

    // ─── Handle ──────────────────────────────────────────────

    public function handle(array $argv): int
    {
        $commandName = $argv[1] ?? null;

        if ($commandName === null || in_array($commandName, ['help', '--help', '-h'])) {
            $this->showHelp();
            return 0;
        }

        $commandClass = $this->find($commandName);

        if ($commandClass === null) {
            echo "\033[31mCommand [{$commandName}] not found.\033[0m" . PHP_EOL;
            $this->showHelp();
            return 1;
        }

        /** @var Command $command */
        $command = $this->app->make($commandClass);
        $command->setInput($argv);

        try {
            return $command->handle();
        } catch (\Throwable $e) {
            echo "\033[31mError: {$e->getMessage()}\033[0m" . PHP_EOL;

            if ((bool) env('APP_DEBUG', false)) {
                echo $e->getTraceAsString() . PHP_EOL;
            }

            return 1;
        }
    }

    // ─── Find ────────────────────────────────────────────────

    private function find(string $name): ?string
    {
        // Tam eşleşme
        if (isset($this->commands[$name])) {
            return $this->commands[$name];
        }

        // Kısmi eşleşme
        foreach ($this->commands as $signature => $class) {
            if (str_starts_with($signature, $name)) {
                return $class;
            }
        }

        return null;
    }

    // ─── Help ────────────────────────────────────────────────

    private function showHelp(): void
    {
        echo PHP_EOL;
        echo "\033[32m🦔 Kirpi Framework CLI\033[0m" . PHP_EOL;
        echo str_repeat('-', 50) . PHP_EOL;
        echo PHP_EOL;

        $rows = [];
        foreach ($this->commands as $signature => $class) {
            $instance = new $class();
            $rows[]   = [$signature, $instance->getDescription()];
        }

        // Komutları grupla
        $groups = [];
        foreach ($rows as [$sig, $desc]) {
            $group = str_contains($sig, ':')
                ? explode(':', $sig)[0]
                : 'general';
            $groups[$group][] = [$sig, $desc];
        }

        foreach ($groups as $group => $cmds) {
            echo "\033[33m" . ucfirst($group) . "\033[0m" . PHP_EOL;
            foreach ($cmds as [$sig, $desc]) {
                echo "  \033[32m" . str_pad($sig, 30) . "\033[0m {$desc}" . PHP_EOL;
            }
            echo PHP_EOL;
        }
    }

    // ─── Core Komutlar ───────────────────────────────────────

    private function registerCoreCommands(): void
    {
        $this->registerMany([
            Commands\MigrateCommand::class,
            Commands\MigrateRollbackCommand::class,
            Commands\MigrateFreshCommand::class,
            Commands\MigrateStatusCommand::class,
            Commands\MakeMigrationCommand::class,
            Commands\MakeModuleCommand::class,
            Commands\MakeCrudCommand::class,
            Commands\SetupCommand::class,
            Commands\SetupAdminCommand::class,
            Commands\SetupRolesCommand::class,
            Commands\SetupCheckCommand::class,
            Commands\QueueWorkCommand::class,
            Commands\CacheClearCommand::class,
            Commands\KeyGenerateCommand::class,
        ]);
    }
}
