<?php

declare(strict_types=1);

namespace Core\Console\Commands;

use Core\Config\EnvLoader;
use Core\Console\Command;

class SetupCommand extends Command
{
    protected string $signature = 'setup';
    protected string $description = 'Interactive setup for local Docker or cloud (Dokploy) deployment';

    public function handle(): int
    {
        $nonInteractive = (bool) $this->option('non-interactive', false);
        $profileOption = strtolower(trim((string) $this->option('profile', '')));

        $this->line();
        $this->info('Kirpi Setup Wizard');
        $this->line(str_repeat('-', 64));

        $profile = $this->resolveProfile($profileOption, $nonInteractive);
        $preflight = $this->runPreflight($profile, $nonInteractive);
        if (($preflight['fatal'] ?? false) === true) {
            $this->error('Setup stopped due to missing prerequisites.');
            $this->line('Fix prerequisites and run setup again.');
            return 1;
        }

        $this->ensureEnvFile();
        $tablerSync = $this->ensureTablerAssets();

        $projectName = $this->resolveProjectName($nonInteractive);
        $appUrl = $this->resolveAppUrl($profile, $nonInteractive);
        $dbConfig = $this->resolveDatabaseConfig($profile, $nonInteractive, $projectName);
        $secretConfig = $this->resolveSecretConfig($nonInteractive);
        $admin = $this->resolveAdminConfig($appUrl, $nonInteractive);

        $updates = [
            'APP_NAME' => $projectName,
            'APP_URL' => $appUrl,
            'KIRPI_DEPLOY_TARGET' => $profile,
            'DB_CONNECTION' => 'mysql',
            'DB_HOST' => $dbConfig['host'],
            'DB_PORT' => (string) $dbConfig['port'],
            'DB_DATABASE' => $dbConfig['database'],
            'DB_USERNAME' => $dbConfig['username'],
            'DB_PASSWORD' => $dbConfig['password'],
            'APP_KEY' => $secretConfig['app_key'],
            'JWT_SECRET' => $secretConfig['jwt_secret'],
            'KIRPI_MANAGER_TOKEN' => $secretConfig['manager_token'],
            'MANAGER_SECRET' => $secretConfig['manager_secret'],
        ];

        if ($dbConfig['mode'] === 'internal') {
            $updates['DB_ROOT_PASSWORD'] = $dbConfig['root_password'];
        }

        $this->writeEnvValues($updates);
        EnvLoader::reload(BASE_PATH);

        $actions = [];
        $healthChecks = [];
        $status = 'ok';
        $errors = [];

        if (($tablerSync['ok'] ?? false) !== true && !($tablerSync['has_local_assets'] ?? false)) {
            $status = 'partial';
            $errors[] = 'Tabler assets are missing and auto-sync failed.';
        }

        if ($profile === 'local') {
            $actions[] = $this->runShell('docker compose up -d --build');
            $this->countdown(12, 'Containers warming up');
            $actions[] = $this->runShellWithRetry(
                'docker compose exec -T app php framework migrate',
                retries: 6,
                sleepSeconds: 4
            );
            $actions[] = $this->runShellWithRetry(
                sprintf(
                    'docker compose exec -T app php framework setup:admin --name=%s --email=%s --password=%s',
                    $this->escapeShellArg($admin['name']),
                    $this->escapeShellArg($admin['email']),
                    $this->escapeShellArg($admin['password'])
                ),
                retries: 3,
                sleepSeconds: 2
            );

            $healthChecks[] = $this->checkHttp($appUrl . '/health');
            $healthChecks[] = $this->checkHttp($appUrl . '/ready');
        } else {
            $healthChecks[] = $this->checkHttp($appUrl . '/health');
            $healthChecks[] = $this->checkHttp($appUrl . '/ready');
            $this->writeCloudGuide($appUrl, $admin['email']);
        }

        foreach ($actions as $action) {
            if (($action['exit_code'] ?? 1) !== 0) {
                $status = 'partial';
                $errors[] = 'Action failed: ' . ($action['command'] ?? 'unknown');
            }
        }

        foreach ($healthChecks as $check) {
            if (($check['ok'] ?? false) !== true) {
                $status = 'partial';
                $errors[] = 'Health check failed: ' . ($check['url'] ?? 'unknown');
            }
        }

        $reportPath = $this->writeReport([
            'status' => $status,
            'generated_at' => date('c'),
            'profile' => $profile,
            'project' => [
                'name' => $projectName,
                'app_url' => $appUrl,
            ],
            'database' => [
                'mode' => $dbConfig['mode'],
                'host' => $dbConfig['host'],
                'port' => $dbConfig['port'],
                'database' => $dbConfig['database'],
                'username' => $dbConfig['username'],
            ],
            'secrets' => [
                'mode' => $secretConfig['mode'],
                'generated' => $secretConfig['mode'] === 'auto',
            ],
            'admin' => [
                'name' => $admin['name'],
                'email' => $admin['email'],
            ],
            'actions' => $actions,
            'health_checks' => $healthChecks,
            'errors' => $errors,
            'preflight' => $preflight,
            'tabler_sync' => $tablerSync,
        ]);

        $this->line();
        $this->table(
            ['Key', 'Value'],
            [
                ['Profile', $profile],
                ['App URL', $appUrl],
                ['DB Mode', $dbConfig['mode']],
                ['Admin Email', $admin['email']],
                ['Tabler', (string) ($tablerSync['status'] ?? 'unknown')],
                ['Report', $reportPath],
                ['Status', $status],
            ]
        );

        if ($profile === 'local' && $status !== 'ok') {
            $this->warning('Local setup completed with warnings. Check report and docker logs.');
        } elseif ($profile === 'local') {
            $this->success('Local setup completed successfully.');
        } elseif ($status === 'ok') {
            $this->success('Cloud setup configuration completed. Deploy on Dokploy and rerun health checks.');
        } else {
            $this->warning('Cloud setup file generation completed with warnings. Check report.');
        }

        $this->line();
        $this->comment('Initial admin password (store it securely):');
        $this->line($admin['password']);

        return $status === 'ok' ? 0 : 1;
    }

    private function ensureEnvFile(): void
    {
        $envPath = BASE_PATH . '/.env';
        if (file_exists($envPath)) {
            return;
        }

        $examplePath = BASE_PATH . '/.env.example';
        if (!file_exists($examplePath)) {
            throw new \RuntimeException('.env and .env.example files are missing.');
        }

        copy($examplePath, $envPath);
        $this->info('.env file created from .env.example');
    }

    /**
     * @return array{fatal:bool, checks:array<int, array<string, mixed>>}
     */
    private function runPreflight(string $profile, bool $nonInteractive): array
    {
        $checks = [];
        $fatal = false;

        $phpOk = version_compare(PHP_VERSION, '8.4.0', '>=');
        $checks[] = [
            'name' => 'php',
            'ok' => $phpOk,
            'message' => $phpOk ? 'PHP ' . PHP_VERSION : 'PHP 8.4+ required (current: ' . PHP_VERSION . ')',
        ];
        if (!$phpOk) {
            $fatal = true;
        }

        $composerOk = $this->isCommandAvailable('composer');
        $checks[] = [
            'name' => 'composer',
            'ok' => $composerOk,
            'message' => $composerOk
                ? 'Composer detected'
                : 'Composer not found (setup can fallback to Docker composer image if Docker exists).',
        ];

        if ($profile === 'local') {
            $dockerOk = $this->isCommandAvailable('docker');
            $checks[] = [
                'name' => 'docker',
                'ok' => $dockerOk,
                'message' => $dockerOk ? 'Docker CLI detected' : 'Docker CLI not found',
            ];

            if (!$dockerOk) {
                $installed = $this->tryInstallDockerDesktop($nonInteractive);
                if ($installed) {
                    $dockerOk = $this->isCommandAvailable('docker');
                    $checks[] = [
                        'name' => 'docker-install',
                        'ok' => $dockerOk,
                        'message' => $dockerOk ? 'Docker Desktop installed' : 'Docker Desktop installation attempt failed',
                    ];
                }
            }

            if (!$dockerOk) {
                $fatal = true;
            } else {
                $daemonOk = $this->isDockerDaemonReady();
                $checks[] = [
                    'name' => 'docker-daemon',
                    'ok' => $daemonOk,
                    'message' => $daemonOk
                        ? 'Docker daemon is running'
                        : 'Docker daemon is not running (start Docker Desktop and retry).',
                ];

                if (!$daemonOk) {
                    $fatal = true;
                }
            }
        }

        $gitOk = $this->isCommandAvailable('git');
        $checks[] = [
            'name' => 'git',
            'ok' => $gitOk,
            'message' => $gitOk
                ? 'Git detected (required for automatic Tabler sync).'
                : 'Git not found (Tabler auto-sync may fail if assets are missing).',
        ];

        $this->line();
        $this->info('Preflight checks');
        $rows = array_map(
            static fn(array $check): array => [
                $check['name'] ?? '-',
                ($check['ok'] ?? false) ? 'ok' : 'fail',
                $check['message'] ?? '',
            ],
            $checks
        );
        $this->table(['Check', 'Status', 'Message'], $rows);

        return [
            'fatal' => $fatal,
            'checks' => $checks,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function ensureTablerAssets(): array
    {
        $tablerDir = BASE_PATH . '/public/vendor/tabler';
        $requiredFiles = [
            $tablerDir . '/dist/css/tabler.min.css',
            $tablerDir . '/static/logo-small.svg',
            $tablerDir . '/layout-fluid.html',
        ];

        $hasLocalAssets = $this->allFilesExist($requiredFiles);
        $ref = trim((string) env('KIRPI_TABLER_REF', 'main'));
        if ($ref === '') {
            $ref = 'main';
        }

        $this->line();
        $this->info('Tabler assets sync');
        $this->line('Target ref: ' . $ref);

        if (!$this->isCommandAvailable('git')) {
            if ($hasLocalAssets) {
                $this->warning('Git missing, using existing local Tabler assets.');
                return [
                    'ok' => true,
                    'status' => 'existing-local',
                    'has_local_assets' => true,
                    'ref' => $ref,
                ];
            }

            $this->warning('Git missing and Tabler assets not found.');
            return [
                'ok' => false,
                'status' => 'missing-git',
                'has_local_assets' => false,
                'ref' => $ref,
            ];
        }

        $tmpRoot = BASE_PATH . '/storage/setup/tmp';
        if (!is_dir($tmpRoot)) {
            mkdir($tmpRoot, 0755, true);
        }

        $tmpDir = $tmpRoot . '/tabler-' . date('YmdHis') . '-' . bin2hex(random_bytes(3));
        $resolvedRef = $ref;
        $cloneCandidates = array_values(array_unique([$ref, 'main']));
        $clone = ['exit_code' => 1];

        foreach ($cloneCandidates as $candidate) {
            $resolvedRef = $candidate;
            $clone = $this->runShell(
                sprintf(
                    'git clone --depth 1 --single-branch --branch %s https://github.com/tabler/tabler.git %s',
                    $this->escapeShellArg($candidate),
                    $this->escapeShellArg($tmpDir)
                )
            );

            if (($clone['exit_code'] ?? 1) === 0) {
                break;
            }
        }

        if (($clone['exit_code'] ?? 1) !== 0 || !is_dir($tmpDir)) {
            $this->warning('Tabler auto-sync failed. Existing assets will be used if available.');
            return [
                'ok' => $hasLocalAssets,
                'status' => $hasLocalAssets ? 'existing-local' : 'clone-failed',
                'has_local_assets' => $hasLocalAssets,
                'ref' => $resolvedRef,
            ];
        }

        try {
            if (!is_dir($tablerDir)) {
                mkdir($tablerDir, 0755, true);
            }

            $layoutBackupPath = null;
            $customLayoutPath = $tablerDir . '/kirpi-layout.html';
            if (is_file($customLayoutPath)) {
                $layoutBackupPath = $tmpRoot . '/kirpi-layout.backup-' . bin2hex(random_bytes(3)) . '.html';
                copy($customLayoutPath, $layoutBackupPath);
            }

            $this->syncDirectory($tmpDir . '/dist', $tablerDir . '/dist');
            $this->syncDirectory($tmpDir . '/static', $tablerDir . '/static');
            $this->syncDirectory($tmpDir . '/preview/pages', BASE_PATH . '/ai-skills/references/tabler/pages');
            $this->deleteDirectory($tablerDir . '/preview');

            if (is_file($tmpDir . '/layout-fluid.html')) {
                copy($tmpDir . '/layout-fluid.html', $tablerDir . '/layout-fluid.html');
            }

            if ($layoutBackupPath !== null && is_file($layoutBackupPath)) {
                copy($layoutBackupPath, $customLayoutPath);
                @unlink($layoutBackupPath);
            }

            $hasLocalAssets = $this->allFilesExist($requiredFiles);
            $this->success('Tabler assets synchronized.');

            return [
                'ok' => $hasLocalAssets,
                'status' => $hasLocalAssets ? 'synced' : 'incomplete',
                'has_local_assets' => $hasLocalAssets,
                'ref' => $resolvedRef,
            ];
        } catch (\Throwable $e) {
            $this->warning('Tabler sync exception: ' . $e->getMessage());
            return [
                'ok' => $hasLocalAssets,
                'status' => $hasLocalAssets ? 'existing-local' : 'sync-exception',
                'has_local_assets' => $hasLocalAssets,
                'ref' => $resolvedRef,
                'error' => $e->getMessage(),
            ];
        } finally {
            $this->deleteDirectory($tmpDir);
        }
    }

    private function allFilesExist(array $files): bool
    {
        foreach ($files as $file) {
            if (!is_file((string) $file)) {
                return false;
            }
        }

        return true;
    }

    private function syncDirectory(string $source, string $target): void
    {
        if (!is_dir($source)) {
            return;
        }

        if (is_dir($target)) {
            $this->deleteDirectory($target);
        }

        mkdir($target, 0755, true);
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $relative = substr($item->getPathname(), strlen($source));
            $dest = $target . $relative;

            if ($item->isDir()) {
                if (!is_dir($dest)) {
                    mkdir($dest, 0755, true);
                }
                continue;
            }

            $parent = dirname($dest);
            if (!is_dir($parent)) {
                mkdir($parent, 0755, true);
            }
            copy($item->getPathname(), $dest);
        }
    }

    private function deleteDirectory(string $path): void
    {
        if (!is_dir($path)) {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $item) {
            if ($item->isDir()) {
                @rmdir($item->getPathname());
            } else {
                @unlink($item->getPathname());
            }
        }

        @rmdir($path);
    }

    private function isCommandAvailable(string $command): bool
    {
        $probe = DIRECTORY_SEPARATOR === '\\'
            ? 'where ' . $command
            : 'command -v ' . $command;

        $output = [];
        $code = 1;
        exec($probe . ' 2>&1', $output, $code);
        return $code === 0;
    }

    private function isDockerDaemonReady(): bool
    {
        $output = [];
        $code = 1;
        exec('docker info 2>&1', $output, $code);
        return $code === 0;
    }

    private function tryInstallDockerDesktop(bool $nonInteractive): bool
    {
        if (DIRECTORY_SEPARATOR !== '\\') {
            return false;
        }

        if (!$this->isCommandAvailable('winget')) {
            return false;
        }

        if (!$nonInteractive) {
            $install = $this->confirm('Docker not found. Attempt install via winget now?', false);
            if (!$install) {
                return false;
            }
        }

        $this->line();
        $this->comment('Attempting Docker Desktop installation via winget...');
        $result = $this->runShell('winget install --id Docker.DockerDesktop -e --accept-package-agreements --accept-source-agreements');

        return ($result['exit_code'] ?? 1) === 0;
    }

    private function resolveProfile(string $profileOption, bool $nonInteractive): string
    {
        if (in_array($profileOption, ['local', 'cloud'], true)) {
            return $profileOption;
        }

        if ($nonInteractive) {
            return 'local';
        }

        $choice = trim($this->ask('Install profile (1=local, 2=cloud)', '1'));
        return $choice === '2' ? 'cloud' : 'local';
    }

    private function resolveProjectName(bool $nonInteractive): string
    {
        $default = (string) env('APP_NAME', basename(BASE_PATH));
        if ($nonInteractive) {
            return $default;
        }

        return trim($this->ask('Project name', $default)) ?: $default;
    }

    private function resolveAppUrl(string $profile, bool $nonInteractive): string
    {
        $default = $profile === 'cloud'
            ? (string) env('APP_URL', 'https://example.com')
            : (string) env('APP_URL', 'http://localhost');

        if ($nonInteractive) {
            return $this->normalizeUrl($default, $profile);
        }

        $raw = trim($this->ask('App URL / domain', $default));
        return $this->normalizeUrl($raw, $profile);
    }

    private function resolveDatabaseConfig(string $profile, bool $nonInteractive, string $projectName): array
    {
        $defaultMode = $profile === 'local' ? 'internal' : 'external';
        $mode = $defaultMode;

        if (!$nonInteractive) {
            $choice = trim($this->ask('Database mode (1=internal docker, 2=external)', $defaultMode === 'internal' ? '1' : '2'));
            $mode = $choice === '2' ? 'external' : 'internal';
        }

        $slug = $this->slug($projectName);
        $databaseDefault = (string) env('DB_DATABASE', $slug !== '' ? $slug : 'kirpi');
        $usernameDefault = (string) env('DB_USERNAME', $slug !== '' ? $slug : 'kirpi');

        if ($mode === 'internal') {
            $config = [
                'mode' => 'internal',
                'host' => 'mysql',
                'port' => 3306,
                'database' => $databaseDefault,
                'username' => $usernameDefault,
                'password' => (string) env('DB_PASSWORD', $this->randomToken(18)),
                'root_password' => (string) env('DB_ROOT_PASSWORD', $this->randomToken(22)),
            ];

            $this->line();
            $this->info('Internal DB mode selected. Database credentials are auto-generated and configured.');
            $this->table(
                ['Key', 'Value'],
                [
                    ['DB_HOST', $config['host']],
                    ['DB_PORT', (string) $config['port']],
                    ['DB_DATABASE', $config['database']],
                    ['DB_USERNAME', $config['username']],
                    ['DB_PASSWORD', str_repeat('*', max(8, strlen((string) $config['password'])))],
                    ['DB_ROOT_PASSWORD', str_repeat('*', max(10, strlen((string) $config['root_password'])))],
                ]
            );

            return $config;
        }

        $hostDefault = (string) env('DB_HOST', '127.0.0.1');
        $portDefault = (int) env('DB_PORT', 3306);
        $passwordDefault = (string) env('DB_PASSWORD', '');

        if ($nonInteractive) {
            return [
                'mode' => 'external',
                'host' => $hostDefault,
                'port' => $portDefault,
                'database' => $databaseDefault,
                'username' => $usernameDefault,
                'password' => $passwordDefault,
                'root_password' => '',
            ];
        }

        return [
            'mode' => 'external',
            'host' => trim($this->ask('DB host', $hostDefault)) ?: $hostDefault,
            'port' => (int) (trim($this->ask('DB port', (string) $portDefault)) ?: $portDefault),
            'database' => trim($this->ask('DB database', $databaseDefault)) ?: $databaseDefault,
            'username' => trim($this->ask('DB username', $usernameDefault)) ?: $usernameDefault,
            'password' => trim($this->ask('DB password', $passwordDefault)) ?: $passwordDefault,
            'root_password' => '',
        ];
    }

    private function resolveSecretConfig(bool $nonInteractive): array
    {
        $mode = 'auto';
        if (!$nonInteractive) {
            $choice = trim($this->ask('Secret mode (1=auto, 2=manual)', '1'));
            $mode = $choice === '2' ? 'manual' : 'auto';
        }

        $defaults = [
            'app_key' => (string) env('APP_KEY', 'kirpi_' . bin2hex(random_bytes(32))),
            'jwt_secret' => (string) env('JWT_SECRET', bin2hex(random_bytes(32))),
            'manager_token' => (string) env('KIRPI_MANAGER_TOKEN', bin2hex(random_bytes(24))),
            'manager_secret' => (string) env('MANAGER_SECRET', bin2hex(random_bytes(24))),
        ];

        if ($mode === 'auto' || $nonInteractive) {
            return ['mode' => $mode] + $defaults;
        }

        return [
            'mode' => 'manual',
            'app_key' => trim($this->ask('APP_KEY', $defaults['app_key'])) ?: $defaults['app_key'],
            'jwt_secret' => trim($this->ask('JWT_SECRET', $defaults['jwt_secret'])) ?: $defaults['jwt_secret'],
            'manager_token' => trim($this->ask('KIRPI_MANAGER_TOKEN', $defaults['manager_token'])) ?: $defaults['manager_token'],
            'manager_secret' => trim($this->ask('MANAGER_SECRET', $defaults['manager_secret'])) ?: $defaults['manager_secret'],
        ];
    }

    private function resolveAdminConfig(string $appUrl, bool $nonInteractive): array
    {
        $host = parse_url($appUrl, PHP_URL_HOST);
        $host = is_string($host) && $host !== '' ? $host : 'localhost';

        $defaultName = 'Kirpi Admin';
        $defaultEmail = 'admin@' . preg_replace('/^www\./', '', $host);
        if (str_contains($defaultEmail, '@localhost')) {
            $defaultEmail = 'admin@kirpi.local';
        }
        $defaultPassword = $this->randomToken(14);

        if ($nonInteractive) {
            return [
                'name' => $defaultName,
                'email' => $defaultEmail,
                'password' => $defaultPassword,
            ];
        }

        $name = trim($this->ask('Initial admin name', $defaultName)) ?: $defaultName;
        $email = trim($this->ask('Initial admin email', $defaultEmail)) ?: $defaultEmail;
        $password = trim($this->ask('Initial admin password (leave empty for auto)', ''));
        if ($password === '') {
            $password = $defaultPassword;
        }

        return [
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ];
    }

    private function normalizeUrl(string $url, string $profile): string
    {
        $value = trim($url);
        if ($value === '') {
            return $profile === 'cloud' ? 'https://example.com' : 'http://localhost';
        }

        if (!str_starts_with($value, 'http://') && !str_starts_with($value, 'https://')) {
            $value = ($profile === 'cloud' ? 'https://' : 'http://') . $value;
        }

        return rtrim($value, '/');
    }

    private function slug(string $value): string
    {
        $slug = strtolower(trim($value));
        $slug = preg_replace('/[^a-z0-9]+/', '_', $slug) ?? '';
        $slug = trim($slug, '_');
        return $slug;
    }

    private function randomToken(int $length): string
    {
        return substr(bin2hex(random_bytes(max(8, $length))), 0, $length);
    }

    private function writeEnvValues(array $updates): void
    {
        $envPath = BASE_PATH . '/.env';
        $content = file_exists($envPath) ? (string) file_get_contents($envPath) : '';

        foreach ($updates as $key => $value) {
            $content = $this->setEnvValue($content, (string) $key, (string) $value);
        }

        file_put_contents($envPath, $content);
    }

    private function setEnvValue(string $content, string $key, string $value): string
    {
        $escapedValue = $this->escapeEnvValue($value);
        $line = $key . '=' . $escapedValue;
        $pattern = '/^' . preg_quote($key, '/') . '=.*$/m';

        if (preg_match($pattern, $content) === 1) {
            return (string) preg_replace($pattern, $line, $content);
        }

        if ($content !== '' && !str_ends_with($content, "\n")) {
            $content .= "\n";
        }

        return $content . $line . "\n";
    }

    private function escapeEnvValue(string $value): string
    {
        if ($value === '') {
            return '';
        }

        if (preg_match('/[\s"#]/', $value) === 1) {
            return '"' . str_replace('"', '\"', $value) . '"';
        }

        return $value;
    }

    private function runShell(string $command): array
    {
        $output = [];
        $exitCode = 1;
        exec($command . ' 2>&1', $output, $exitCode);

        $this->line();
        $this->comment('> ' . $command);
        foreach ($output as $line) {
            $this->line($line);
        }

        return [
            'command' => $command,
            'exit_code' => $exitCode,
            'output' => $output,
        ];
    }

    private function runShellWithRetry(string $command, int $retries = 3, int $sleepSeconds = 2): array
    {
        $attempt = 1;
        $last = [];
        while ($attempt <= $retries) {
            $result = $this->runShell($command);
            $result['attempt'] = $attempt;
            $last = $result;

            if (($result['exit_code'] ?? 1) === 0) {
                return $result;
            }

            if ($attempt < $retries) {
                $this->warning("Command failed (attempt {$attempt}/{$retries}). Retrying in {$sleepSeconds}s...");
                sleep($sleepSeconds);
            }

            $attempt++;
        }

        return $last;
    }

    private function checkHttp(string $url): array
    {
        $start = microtime(true);
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => 5,
                'ignore_errors' => true,
            ],
        ]);

        $body = @file_get_contents($url, false, $context);
        $durationMs = round((microtime(true) - $start) * 1000, 2);

        $status = 0;
        $headers = $http_response_header ?? [];
        if (isset($headers[0]) && preg_match('/\s(\d{3})\s/', $headers[0], $matches)) {
            $status = (int) $matches[1];
        }

        return [
            'url' => $url,
            'ok' => $status >= 200 && $status < 300,
            'status_code' => $status,
            'duration_ms' => $durationMs,
            'body_excerpt' => is_string($body) ? substr(trim($body), 0, 180) : null,
        ];
    }

    private function escapeShellArg(string $value): string
    {
        if (DIRECTORY_SEPARATOR === '\\') {
            return '"' . str_replace('"', '\"', $value) . '"';
        }

        return escapeshellarg($value);
    }

    private function countdown(int $seconds, string $label = 'Waiting'): void
    {
        if ($seconds <= 0) {
            return;
        }

        $this->line();
        for ($i = $seconds; $i >= 1; $i--) {
            $this->comment("{$label}: {$i}s");
            sleep(1);
        }
    }

    private function writeReport(array $report): string
    {
        $dir = BASE_PATH . '/storage/setup';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $filename = 'setup-' . date('Ymd-His') . '.json';
        $path = $dir . '/' . $filename;
        file_put_contents($path, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return 'storage/setup/' . $filename;
    }

    private function writeCloudGuide(string $appUrl, string $adminEmail): void
    {
        $dir = BASE_PATH . '/storage/setup';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $guidePath = $dir . '/dokploy-next-steps.md';
        $content = <<<MD
# Dokploy Deployment Notes

- APP_URL: {$appUrl}
- Admin email: {$adminEmail}

## Suggested deploy flow
1. Push updated `.env` and project files to your Git repository.
2. Create a Dokploy project and set build/deploy to this repository.
3. Add environment variables from `.env` (production-safe values).
4. Run migrations after first deploy:
   - `php framework migrate`
5. Create/update admin user:
   - `php framework setup:admin --name="Kirpi Admin" --email="{$adminEmail}" --password="<secure-password>"`
6. Validate endpoints:
   - `{$appUrl}/health`
   - `{$appUrl}/ready`
MD;

        file_put_contents($guidePath, $content);
    }
}
