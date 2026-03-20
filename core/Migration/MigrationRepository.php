<?php

declare(strict_types=1);

namespace Core\Migration;

use Core\Database\DatabaseManager;

class MigrationRepository
{
    private string $table = 'migrations';

    public function __construct(
        private readonly DatabaseManager $db,
    ) {}

    public function createRepositoryIfNotExists(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS `{$this->table}` (
            `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `migration`  VARCHAR(255) NOT NULL,
            `batch`      INT NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        $this->db->connection()->statement($sql);
    }

    public function getRan(): array
    {
        $results = $this->db->table($this->table)
            ->orderBy('id')
            ->pluck('migration');

        return $results;
    }

    public function getAll(): array
    {
        return $this->db->table($this->table)
            ->orderBy('batch')
            ->orderBy('id')
            ->get()
            ->toArray();
    }

    public function getLastBatch(int $steps = 1): array
    {
        $lastBatch = $this->getLastBatchNumber();

        if ($lastBatch === 0) return [];

        $minBatch = $lastBatch - $steps + 1;

        return $this->db->table($this->table)
            ->where('batch', '>=', $minBatch)
            ->orderByDesc('id')
            ->get()
            ->toArray();
    }

    public function getNextBatchNumber(): int
    {
        return $this->getLastBatchNumber() + 1;
    }

    public function getLastBatchNumber(): int
{
    $result = $this->db->raw(
        "SELECT MAX(batch) as max_batch FROM `{$this->table}`"
    );

    return (int) ($result[0]->max_batch ?? 0);
}

    public function getBatch(string $migration): ?int
    {
        $result = $this->db->table($this->table)
            ->where('migration', $migration)
            ->first();

        return $result ? (int) $result->batch : null;
    }

    public function log(string $migration, int $batch): void
    {
        $this->db->table($this->table)->insert([
            'migration'  => $migration,
            'batch'      => $batch,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function delete(string $migration): void
    {
        $this->db->table($this->table)
            ->where('migration', $migration)
            ->delete();
    }

    public function isInstalled(string $migration): bool
    {
        return $this->db->table($this->table)
            ->where('migration', $migration)
            ->exists();
    }
}