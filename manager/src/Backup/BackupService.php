<?php

declare(strict_types=1);

namespace Manager\Backup;

final class BackupService
{
    private const DATE_FORMAT = 'Ymd_His';

    public function __construct(
        private readonly string $backupDir = '',
    ) {}

    /**
     * @return array{ok:bool,file?:string,path?:string,size_bytes?:int,sha256?:string,created_at?:string,error?:string,details?:array<string,mixed>}
     */
    public function create(string $mode = 'full'): array
    {
        $mode = strtolower(trim($mode));
        if (!in_array($mode, ['full', 'db'], true)) {
            return ['ok' => false, 'error' => 'Unsupported backup mode. Use full or db.'];
        }

        $backupDir = $this->resolveBackupDir();
        if (!is_dir($backupDir) && !@mkdir($backupDir, 0775, true) && !is_dir($backupDir)) {
            return ['ok' => false, 'error' => 'Backup directory is not writable.'];
        }

        $timestamp = date(self::DATE_FORMAT);
        $name = sprintf('kirpi-%s-%s.zip', $mode, $timestamp);
        $target = $backupDir . DIRECTORY_SEPARATOR . $name;

        $tmp = $backupDir . DIRECTORY_SEPARATOR . '_tmp_' . $timestamp . '_' . bin2hex(random_bytes(4));
        if (!@mkdir($tmp, 0775, true) && !is_dir($tmp)) {
            return ['ok' => false, 'error' => 'Failed to create temporary backup directory.'];
        }

        try {
            $dbResult = $this->dumpDatabase($tmp);
            if (!$dbResult['ok']) {
                return $dbResult;
            }

            $files = [];
            $files['db.sql'] = $tmp . DIRECTORY_SEPARATOR . 'db.sql';

            $envPath = base_path('.env');
            if (is_file($envPath)) {
                copy($envPath, $tmp . DIRECTORY_SEPARATOR . '.env');
                $files['.env'] = $tmp . DIRECTORY_SEPARATOR . '.env';
            }

            if ($mode === 'full') {
                $storageArchive = $tmp . DIRECTORY_SEPARATOR . 'storage-app.zip';
                $storageDir = storage_path('app');
                if (is_dir($storageDir)) {
                    $archiveOk = $this->zipDirectory($storageDir, $storageArchive);
                    if ($archiveOk) {
                        $files['storage-app.zip'] = $storageArchive;
                    }
                }
            }

            $manifestPath = $tmp . DIRECTORY_SEPARATOR . 'manifest.json';
            $manifest = [
                'created_at' => date('Y-m-d H:i:s'),
                'mode' => $mode,
                'app_env' => (string) env('APP_ENV', 'local'),
                'app_version' => (string) config('app.version', '1.0.0'),
                'php' => PHP_VERSION,
                'db_connection' => (string) env('DB_CONNECTION', 'mysql'),
                'files' => [],
            ];

            foreach ($files as $logical => $path) {
                if (!is_file($path)) {
                    continue;
                }

                $manifest['files'][$logical] = [
                    'size_bytes' => filesize($path) ?: 0,
                    'sha256' => hash_file('sha256', $path),
                ];
            }

            file_put_contents($manifestPath, json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            $files['manifest.json'] = $manifestPath;

            if (!$this->zipFiles($files, $target, $tmp)) {
                return ['ok' => false, 'error' => 'Failed to build backup archive (zip extension required).'];
            }

            $archiveHash = hash_file('sha256', $target);
            file_put_contents($target . '.sha256', $archiveHash . '  ' . basename($target) . PHP_EOL);

            $this->pruneByRetention($backupDir);

            return [
                'ok' => true,
                'file' => basename($target),
                'path' => $target,
                'size_bytes' => filesize($target) ?: 0,
                'sha256' => $archiveHash,
                'created_at' => date('Y-m-d H:i:s'),
                'details' => [
                    'mode' => $mode,
                    'db_method' => $dbResult['details']['method'] ?? 'unknown',
                ],
            ];
        } finally {
            $this->deleteDirectory($tmp);
        }
    }

    /**
     * @return array{ok:bool,data?:array<int,array<string,mixed>>,error?:string}
     */
    public function list(): array
    {
        $backupDir = $this->resolveBackupDir();
        if (!is_dir($backupDir)) {
            return ['ok' => true, 'data' => []];
        }

        $items = [];
        foreach (glob($backupDir . DIRECTORY_SEPARATOR . 'kirpi-*.zip') ?: [] as $path) {
            if (!is_file($path)) {
                continue;
            }

            $hashFile = $path . '.sha256';
            $storedHash = '';
            if (is_file($hashFile)) {
                $line = trim((string) file_get_contents($hashFile));
                $storedHash = explode('  ', $line)[0] ?? '';
            }

            $items[] = [
                'file' => basename($path),
                'size_bytes' => filesize($path) ?: 0,
                'created_at' => date('Y-m-d H:i:s', filemtime($path) ?: time()),
                'sha256_saved' => $storedHash,
                'path' => $path,
            ];
        }

        usort($items, static fn (array $a, array $b): int => strcmp($b['file'], $a['file']));

        return ['ok' => true, 'data' => $items];
    }

    /**
     * @return array{ok:bool,error?:string,data?:array<string,mixed>}
     */
    public function verify(string $file): array
    {
        $resolved = $this->resolveBackupFile($file);
        if ($resolved === null) {
            return ['ok' => false, 'error' => 'Invalid backup file.'];
        }

        if (!is_file($resolved)) {
            return ['ok' => false, 'error' => 'Backup file not found.'];
        }

        $actual = hash_file('sha256', $resolved);
        $hashFile = $resolved . '.sha256';
        $stored = '';
        if (is_file($hashFile)) {
            $line = trim((string) file_get_contents($hashFile));
            $stored = explode('  ', $line)[0] ?? '';
        }

        return [
            'ok' => true,
            'data' => [
                'file' => basename($resolved),
                'sha256_actual' => $actual,
                'sha256_saved' => $stored,
                'valid' => $stored !== '' && hash_equals($stored, $actual),
            ],
        ];
    }

    /**
     * @return array{ok:bool,error?:string}
     */
    public function delete(string $file): array
    {
        $resolved = $this->resolveBackupFile($file);
        if ($resolved === null) {
            return ['ok' => false, 'error' => 'Invalid backup file.'];
        }

        if (!is_file($resolved)) {
            return ['ok' => false, 'error' => 'Backup file not found.'];
        }

        @unlink($resolved);
        @unlink($resolved . '.sha256');

        return ['ok' => true];
    }

    public function resolveBackupFile(string $file): ?string
    {
        $file = trim($file);
        if ($file === '' || !preg_match('/^kirpi-(db|full)-\d{8}_\d{6}\.zip$/', $file)) {
            return null;
        }

        return $this->resolveBackupDir() . DIRECTORY_SEPARATOR . $file;
    }

    private function resolveBackupDir(): string
    {
        if ($this->backupDir !== '') {
            return $this->backupDir;
        }

        $configured = trim((string) env('KIRPI_BACKUP_DIR', ''));
        if ($configured !== '') {
            return $configured;
        }

        return storage_path('backups');
    }

    /**
     * @return array{ok:bool,error?:string,details?:array<string,string>}
     */
    private function dumpDatabase(string $tmpDir): array
    {
        $dumpPath = $tmpDir . DIRECTORY_SEPARATOR . 'db.sql';
        $connection = strtolower((string) env('DB_CONNECTION', 'mysql'));

        if ($connection === 'sqlite') {
            $dbPath = (string) env('DB_DATABASE', '');
            if ($dbPath === '' || !is_file($dbPath)) {
                return ['ok' => false, 'error' => 'SQLite database file not found for backup.'];
            }

            copy($dbPath, $tmpDir . DIRECTORY_SEPARATOR . 'database.sqlite');
            file_put_contents($dumpPath, '-- SQLite backup copied as database.sqlite' . PHP_EOL);

            return ['ok' => true, 'details' => ['method' => 'sqlite-copy']];
        }

        $dbName = (string) env('DB_DATABASE', '');
        $dbUser = (string) env('DB_USERNAME', '');
        $dbPass = (string) env('DB_PASSWORD', '');
        $dbHost = (string) env('DB_HOST', 'mysql');

        if ($dbName === '' || $dbUser === '') {
            return ['ok' => false, 'error' => 'Database credentials are not configured.'];
        }

        $useDocker = filter_var((string) env('KIRPI_BACKUP_USE_DOCKER', 'true'), FILTER_VALIDATE_BOOL);
        $container = (string) env('KIRPI_BACKUP_MYSQL_CONTAINER', 'kirpi_mysql');

        if ($useDocker) {
            $cmd = sprintf(
                'docker exec -e MYSQL_PWD=%s %s mysqldump --single-transaction --routines --triggers -u %s %s > %s',
                escapeshellarg($dbPass),
                escapeshellarg($container),
                escapeshellarg($dbUser),
                escapeshellarg($dbName),
                escapeshellarg($dumpPath)
            );

            $code = $this->runCommand($cmd);
            if ($code === 0 && is_file($dumpPath) && (filesize($dumpPath) ?: 0) > 0) {
                return ['ok' => true, 'details' => ['method' => 'docker-exec']];
            }
        }

        $native = $this->dumpMySqlNative($dumpPath, $dbHost, (int) env('DB_PORT', 3306), $dbName, $dbUser, $dbPass);
        if (($native['ok'] ?? false) === true) {
            return $native;
        }

        $cmd = sprintf(
            'mysqldump --single-transaction --routines --triggers -h %s -u%s -p%s %s > %s',
            escapeshellarg($dbHost),
            escapeshellarg($dbUser),
            escapeshellarg($dbPass),
            escapeshellarg($dbName),
            escapeshellarg($dumpPath)
        );
        $code = $this->runCommand($cmd);

        if ($code === 0 && is_file($dumpPath) && (filesize($dumpPath) ?: 0) > 0) {
            return ['ok' => true, 'details' => ['method' => 'mysqldump']];
        }

        return ['ok' => false, 'error' => 'Database dump failed (docker/native/mysqldump).'];
    }

    private function runCommand(string $command): int
    {
        $output = [];
        $code = 1;
        @exec($command . ' 2>&1', $output, $code);
        return $code;
    }

    /**
     * @return array{ok:bool,error?:string,details?:array<string,string>}
     */
    private function dumpMySqlNative(
        string $dumpPath,
        string $host,
        int $port,
        string $dbName,
        string $user,
        string $password
    ): array {
        if (!class_exists(\PDO::class)) {
            return ['ok' => false, 'error' => 'PDO extension is not available.'];
        }

        try {
            $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $host, $port, $dbName);
            $pdo = new \PDO($dsn, $user, $password, [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            ]);

            $sql = [];
            $sql[] = '-- Kirpi native mysql backup';
            $sql[] = '-- Generated at: ' . date('Y-m-d H:i:s');
            $sql[] = 'SET FOREIGN_KEY_CHECKS=0;';
            $sql[] = '';

            $tablesStmt = $pdo->query('SHOW FULL TABLES WHERE Table_type = "BASE TABLE"');
            $tables = [];
            foreach ($tablesStmt as $row) {
                $tables[] = (string) array_values($row)[0];
            }

            foreach ($tables as $table) {
                $tableQuoted = '`' . str_replace('`', '``', $table) . '`';
                $createStmt = $pdo->query('SHOW CREATE TABLE ' . $tableQuoted)->fetch();
                if (!is_array($createStmt)) {
                    continue;
                }

                $createSql = (string) ($createStmt['Create Table'] ?? '');
                if ($createSql === '') {
                    continue;
                }

                $sql[] = 'DROP TABLE IF EXISTS ' . $tableQuoted . ';';
                $sql[] = $createSql . ';';

                $rowsStmt = $pdo->query('SELECT * FROM ' . $tableQuoted);
                while (($row = $rowsStmt->fetch(\PDO::FETCH_ASSOC)) !== false) {
                    $columns = array_map(
                        static fn (string $col): string => '`' . str_replace('`', '``', $col) . '`',
                        array_keys($row)
                    );
                    $values = [];
                    foreach ($row as $value) {
                        if ($value === null) {
                            $values[] = 'NULL';
                        } elseif (is_bool($value)) {
                            $values[] = $value ? '1' : '0';
                        } else {
                            $values[] = $pdo->quote((string) $value);
                        }
                    }

                    $sql[] = 'INSERT INTO ' . $tableQuoted . ' (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $values) . ');';
                }

                $sql[] = '';
            }

            $sql[] = 'SET FOREIGN_KEY_CHECKS=1;';
            file_put_contents($dumpPath, implode(PHP_EOL, $sql) . PHP_EOL);

            if (!is_file($dumpPath) || (filesize($dumpPath) ?: 0) === 0) {
                return ['ok' => false, 'error' => 'Native mysql dump generated empty file.'];
            }

            return ['ok' => true, 'details' => ['method' => 'native-pdo']];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => 'Native mysql dump failed: ' . $e->getMessage()];
        }
    }

    private function zipDirectory(string $sourceDir, string $zipPath): bool
    {
        if (!class_exists(\ZipArchive::class)) {
            return false;
        }

        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            return false;
        }

        $sourceDir = rtrim($sourceDir, DIRECTORY_SEPARATOR);
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($sourceDir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            /** @var \SplFileInfo $item */
            if (!$item->isFile()) {
                continue;
            }

            $path = $item->getPathname();
            $relative = substr($path, strlen($sourceDir) + 1);
            $zip->addFile($path, $relative);
        }

        $zip->close();
        return true;
    }

    /**
     * @param array<string,string> $files
     */
    private function zipFiles(array $files, string $zipPath, string $baseDir): bool
    {
        if (!class_exists(\ZipArchive::class)) {
            return false;
        }

        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            return false;
        }

        $baseDir = rtrim($baseDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        foreach ($files as $logical => $path) {
            if (!is_file($path)) {
                continue;
            }

            if (str_starts_with($path, $baseDir)) {
                $zipPathInner = str_replace('\\', '/', substr($path, strlen($baseDir)));
                $zip->addFile($path, $zipPathInner);
                continue;
            }

            $zip->addFile($path, $logical);
        }

        $zip->close();
        return true;
    }

    private function pruneByRetention(string $backupDir): void
    {
        $keep = max(1, (int) env('KIRPI_BACKUP_RETENTION', 10));
        $files = glob($backupDir . DIRECTORY_SEPARATOR . 'kirpi-*.zip') ?: [];
        rsort($files, SORT_STRING);

        $excess = array_slice($files, $keep);
        foreach ($excess as $file) {
            @unlink($file);
            @unlink($file . '.sha256');
        }
    }

    private function deleteDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $items = scandir($dir);
        if ($items === false) {
            return;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                @unlink($path);
            }
        }

        @rmdir($dir);
    }
}
