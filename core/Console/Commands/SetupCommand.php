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

        $this->ensureEnvFile();

        $profile = $this->resolveProfile($profileOption, $nonInteractive);
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

        if ($profile === 'local') {
            $actions[] = $this->runShell('docker compose up -d --build');
            $actions[] = $this->runShell('docker compose exec -T app php framework migrate');
            $actions[] = $this->runShell(
                sprintf(
                    'docker compose exec -T app php framework setup:admin --name=%s --email=%s --password=%s',
                    $this->escapeShellArg($admin['name']),
                    $this->escapeShellArg($admin['email']),
                    $this->escapeShellArg($admin['password'])
                )
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
        ]);

        $this->line();
        $this->table(
            ['Key', 'Value'],
            [
                ['Profile', $profile],
                ['App URL', $appUrl],
                ['DB Mode', $dbConfig['mode']],
                ['Admin Email', $admin['email']],
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
            $passwordDefault = (string) env('DB_PASSWORD', $this->randomToken(16));
            $rootPasswordDefault = (string) env('DB_ROOT_PASSWORD', $this->randomToken(20));

            if ($nonInteractive) {
                return [
                    'mode' => 'internal',
                    'host' => 'mysql',
                    'port' => 3306,
                    'database' => $databaseDefault,
                    'username' => $usernameDefault,
                    'password' => $passwordDefault,
                    'root_password' => $rootPasswordDefault,
                ];
            }

            return [
                'mode' => 'internal',
                'host' => 'mysql',
                'port' => 3306,
                'database' => trim($this->ask('DB database', $databaseDefault)) ?: $databaseDefault,
                'username' => trim($this->ask('DB username', $usernameDefault)) ?: $usernameDefault,
                'password' => trim($this->ask('DB password', $passwordDefault)) ?: $passwordDefault,
                'root_password' => trim($this->ask('DB root password', $rootPasswordDefault)) ?: $rootPasswordDefault,
            ];
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
