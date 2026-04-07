<?php

declare(strict_types=1);

namespace Core\Console\Commands;

use Core\Console\Command;
use Core\Database\DatabaseManager;
use Core\Migration\SchemaBuilder;
use Modules\Roles\Models\Role;
use Modules\Roles\Models\RolePermission;
use Modules\Roles\Support\DefaultPermissions;

final class SetupCheckCommand extends Command
{
    protected string $signature = 'setup:check';
    protected string $description = 'Run post-setup smoke checks (db/tables/roles/permissions/http)';

    public function handle(): int
    {
        $skipHttp = (bool) $this->option('skip-http', false);
        $baseUrl = trim((string) $this->option('url', (string) env('APP_URL', 'http://localhost')));

        $rows = [];
        $failed = false;

        try {
            $db = app(DatabaseManager::class);
            $schema = new SchemaBuilder($db);

            $this->appendCheck($rows, $failed, 'db.connection', $this->checkDatabaseConnection($db));
            $this->appendCheck($rows, $failed, 'db.tables', $this->checkTables($schema));
            $this->appendCheck($rows, $failed, 'seed.roles', $this->checkDefaultRoles());
            $this->appendCheck($rows, $failed, 'seed.permissions', $this->checkDefaultRolePermissions());
        } catch (\Throwable $e) {
            $rows[] = ['bootstrap', 'fail', $e->getMessage()];
            $failed = true;
        }

        if (!$skipHttp) {
            $this->appendCheck($rows, $failed, 'http.health', $this->checkHttpEndpoint($baseUrl, '/health'));
            $this->appendCheck($rows, $failed, 'http.ready', $this->checkHttpEndpoint($baseUrl, '/ready'));
        }

        $this->line();
        $this->table(['Check', 'Status', 'Message'], $rows);

        if ($failed) {
            $this->error('Setup check failed.');
            return 1;
        }

        $this->success('Setup check passed.');
        return 0;
    }

    private function appendCheck(array &$rows, bool &$failed, string $name, array $result): void
    {
        $ok = (bool) ($result['ok'] ?? false);
        $rows[] = [$name, $ok ? 'ok' : 'fail', (string) ($result['message'] ?? '')];
        if (!$ok) {
            $failed = true;
        }
    }

    private function checkDatabaseConnection(DatabaseManager $db): array
    {
        try {
            $db->raw('SELECT 1 as ping');
            return ['ok' => true, 'message' => 'Database connection is healthy.'];
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => 'Database query failed: ' . $e->getMessage()];
        }
    }

    private function checkTables(SchemaBuilder $schema): array
    {
        $required = [
            'users',
            'roles',
            'role_permissions',
            'password_reset_tokens',
        ];

        $missing = [];
        foreach ($required as $table) {
            if (!$schema->hasTable($table)) {
                $missing[] = $table;
            }
        }

        if ($missing !== []) {
            return ['ok' => false, 'message' => 'Missing tables: ' . implode(', ', $missing)];
        }

        return ['ok' => true, 'message' => 'Required tables exist.'];
    }

    private function checkDefaultRoles(): array
    {
        $required = ['super-admin', 'admin', 'editor', 'viewer'];
        $missing = [];

        foreach ($required as $slug) {
            $role = Role::query()->select('id')->where('slug', $slug)->first();
            if ($role === null || !isset($role->id)) {
                $missing[] = $slug;
            }
        }

        if ($missing !== []) {
            return ['ok' => false, 'message' => 'Missing default roles: ' . implode(', ', $missing)];
        }

        return ['ok' => true, 'message' => 'Default roles are present.'];
    }

    private function checkDefaultRolePermissions(): array
    {
        $slugs = ['super-admin', 'admin', 'editor', 'viewer'];
        $mismatches = [];

        foreach ($slugs as $slug) {
            $role = Role::query()->select('id')->where('slug', $slug)->first();
            if ($role === null || !isset($role->id)) {
                $mismatches[] = $slug . ' (role-missing)';
                continue;
            }

            $roleId = (int) $role->id;
            $expected = DefaultPermissions::forRoleSlug($slug);
            sort($expected);

            $items = RolePermission::query()
                ->select('permission_key')
                ->where('role_id', $roleId)
                ->where('is_allowed', 1)
                ->get();

            $actual = [];
            foreach ($items as $item) {
                $key = trim((string) ($item->permission_key ?? ''));
                if ($key !== '') {
                    $actual[] = $key;
                }
            }

            $actual = array_values(array_unique($actual));
            sort($actual);

            if ($actual !== $expected) {
                $mismatches[] = $slug;
            }
        }

        if ($mismatches !== []) {
            return ['ok' => false, 'message' => 'Permission mismatch: ' . implode(', ', $mismatches)];
        }

        return ['ok' => true, 'message' => 'Default role permissions are synced.'];
    }

    private function checkHttpEndpoint(string $baseUrl, string $path): array
    {
        $baseUrl = rtrim($baseUrl, '/');
        $url = $baseUrl . $path;
        $candidates = [$url];

        $parsedHost = parse_url($baseUrl, PHP_URL_HOST);
        if (is_string($parsedHost) && in_array($parsedHost, ['localhost', '127.0.0.1'], true)) {
            $candidates[] = 'http://nginx' . $path;
        }

        foreach (array_values(array_unique($candidates)) as $candidate) {
            $result = $this->fetchHttp($candidate);
            if (($result['ok'] ?? false) === true) {
                return [
                    'ok' => true,
                    'message' => sprintf('%s %d', $candidate, (int) ($result['status_code'] ?? 0)),
                ];
            }
        }

        $last = end($candidates);
        return ['ok' => false, 'message' => 'HTTP check failed for: ' . ($last ?: $url)];
    }

    private function fetchHttp(string $url): array
    {
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => 5,
                'ignore_errors' => true,
            ],
        ]);

        @file_get_contents($url, false, $context);
        $headers = $http_response_header ?? [];
        $status = 0;
        if (isset($headers[0]) && preg_match('/\s(\d{3})\s/', $headers[0], $matches)) {
            $status = (int) $matches[1];
        }

        return [
            'ok' => $status >= 200 && $status < 300,
            'status_code' => $status,
        ];
    }
}

