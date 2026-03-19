<?php

declare(strict_types=1);

namespace Core\Migration;

class Migrator
{
    public function __construct(
        private readonly SchemaBuilder       $schema,
        private readonly MigrationRepository $repository,
        private readonly string              $path,
    ) {}

    // ─── Run ─────────────────────────────────────────────────

    public function run(): array
    {
        $this->repository->createRepositoryIfNotExists();

        $ran     = $this->repository->getRan();
        $files   = $this->getMigrationFiles($this->path);
        $pending = array_diff($files, $ran);

        if (empty($pending)) {
            $this->log('Nothing to migrate.');
            return [];
        }

        sort($pending);

        $batch = $this->repository->getNextBatchNumber();
        $ran   = [];

        foreach ($pending as $file) {
            $this->runMigration($file, $this->path, $batch);
            $ran[] = $file;
        }

        return $ran;
    }

    public function runPath(string $path): array
    {
        $this->repository->createRepositoryIfNotExists();

        $ran     = $this->repository->getRan();
        $files   = $this->getMigrationFiles($path);
        $pending = array_diff($files, $ran);

        if (empty($pending)) return [];

        sort($pending);

        $batch = $this->repository->getNextBatchNumber();
        $done  = [];

        foreach ($pending as $file) {
            $this->runMigration($file, $path, $batch);
            $done[] = $file;
        }

        return $done;
    }

    // ─── Rollback ────────────────────────────────────────────

    public function rollback(int $steps = 1): array
    {
        $migrations = $this->repository->getLastBatch($steps);

        if (empty($migrations)) {
            $this->log('Nothing to rollback.');
            return [];
        }

        $rolledBack = [];

        foreach ($migrations as $migration) {
            $this->rollbackMigration($migration, $this->path);
            $rolledBack[] = $migration->migration;
        }

        return $rolledBack;
    }

    public function rollbackPath(string $path): array
    {
        $files      = $this->getMigrationFiles($path);
        $migrations = $this->repository->getAll();
        $rolledBack = [];

        foreach (array_reverse($migrations) as $migration) {
            if (in_array($migration->migration, $files)) {
                $this->rollbackMigration($migration, $path);
                $rolledBack[] = $migration->migration;
            }
        }

        return $rolledBack;
    }

    // ─── Reset & Fresh ───────────────────────────────────────

    public function reset(): array
    {
        $migrations = $this->repository->getAll();
        $reset      = [];

        foreach (array_reverse($migrations) as $migration) {
            $this->rollbackMigration($migration, $this->path);
            $reset[] = $migration->migration;
        }

        return $reset;
    }

    public function fresh(): array
    {
        $this->reset();
        return $this->run();
    }

    // ─── Status ──────────────────────────────────────────────

    public function status(): array
    {
        $this->repository->createRepositoryIfNotExists();

        $ran   = $this->repository->getRan();
        $files = $this->getMigrationFiles($this->path);

        sort($files);

        return array_map(fn($file) => [
            'migration' => $file,
            'ran'       => in_array($file, $ran),
            'batch'     => $this->repository->getBatch($file),
        ], $files);
    }

    // ─── Private ─────────────────────────────────────────────

    private function runMigration(string $file, string $path, int $batch): void
    {
        $migration = $this->resolve($file, $path);

        $this->log("Migrating: {$file}");

        $migration->up($this->schema);

        $this->repository->log($file, $batch);

        $this->log("Migrated:  {$file}");
    }

    private function rollbackMigration(object $record, string $path): void
    {
        $migration = $this->resolve($record->migration, $path);

        $this->log("Rolling back: {$record->migration}");

        $migration->down($this->schema);

        $this->repository->delete($record->migration);

        $this->log("Rolled back:  {$record->migration}");
    }

    private function resolve(string $file, string $path): object
    {
        $filePath = rtrim($path, '/') . '/' . $file . '.php';
        return require $filePath;
    }

    private function getMigrationFiles(string $path): array
    {
        if (!is_dir($path)) return [];

        $files = glob(rtrim($path, '/') . '/*.php');

        return array_map(
            fn($f) => pathinfo($f, PATHINFO_FILENAME),
            $files ?: []
        );
    }

    private function log(string $message): void
    {
        echo "[" . date('H:i:s') . "] " . $message . PHP_EOL;
    }
}