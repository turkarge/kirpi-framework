<?php

declare(strict_types=1);

namespace Tests\Unit;

use Manager\Backup\BackupService;
use PHPUnit\Framework\TestCase;

class BackupServiceTest extends TestCase
{
    public function test_resolve_backup_file_rejects_invalid_name(): void
    {
        $service = new BackupService(storage_path('backups'));

        $this->assertNull($service->resolveBackupFile(''));
        $this->assertNull($service->resolveBackupFile('../kirpi-full-20260101_010101.zip'));
        $this->assertNull($service->resolveBackupFile('kirpi-full-invalid.zip'));
        $this->assertNull($service->resolveBackupFile('random.zip'));
    }

    public function test_resolve_backup_file_accepts_supported_pattern(): void
    {
        $service = new BackupService(storage_path('backups'));

        $resolved = $service->resolveBackupFile('kirpi-full-20260322_180000.zip');

        $this->assertNotNull($resolved);
        $this->assertStringEndsWith('storage\\backups\\kirpi-full-20260322_180000.zip', str_replace('/', '\\', (string) $resolved));
    }
}

