<?php

declare(strict_types=1);

namespace Core\Console\Commands;

use Core\Console\Command;
use Modules\Roles\Models\Role;
use Modules\Roles\Models\RolePermission;
use Modules\Roles\Support\DefaultPermissions;

final class SetupRolesCommand extends Command
{
    protected string $signature = 'setup:roles';
    protected string $description = 'Create/update default system roles';

    public function handle(): int
    {
        $defaults = [
            [
                'name' => 'Super Admin',
                'slug' => 'super-admin',
                'description' => 'Tum sistem yetkilerine sahip cekirdek yonetici rolu.',
                'is_active' => 1,
                'is_system' => 1,
                'sort_order' => 1,
            ],
            [
                'name' => 'Admin',
                'slug' => 'admin',
                'description' => 'Uygulama yonetimi ve operasyon yetkilerine sahip rol.',
                'is_active' => 1,
                'is_system' => 1,
                'sort_order' => 10,
            ],
            [
                'name' => 'Editor',
                'slug' => 'editor',
                'description' => 'Icerik ve operasyon guncellemelerini yoneten rol.',
                'is_active' => 1,
                'is_system' => 1,
                'sort_order' => 20,
            ],
            [
                'name' => 'Viewer',
                'slug' => 'viewer',
                'description' => 'Sadece okuma yetkisine sahip temel rol.',
                'is_active' => 1,
                'is_system' => 1,
                'sort_order' => 30,
            ],
        ];

        try {
            foreach ($defaults as $role) {
                Role::query()
                    ->updateOrInsert(
                        ['slug' => $role['slug']],
                        [
                            'name' => $role['name'],
                            'description' => $role['description'],
                            'is_active' => $role['is_active'],
                            'is_system' => $role['is_system'],
                            'sort_order' => $role['sort_order'],
                            'updated_at' => now(),
                            'created_at' => now(),
                        ]
                    );

                $this->syncDefaultPermissions((string) $role['slug']);
            }

            $this->assertDefaultPermissionsSynced();
            $this->success('Default roles are ready.');
            return 0;
        } catch (\Throwable $e) {
            $this->error('Failed to setup roles: ' . $e->getMessage());
            return 1;
        }
    }

    private function syncDefaultPermissions(string $slug): void
    {
        $role = Role::query()->select('id')->where('slug', $slug)->first();
        if ($role === null || !isset($role->id)) {
            return;
        }

        $roleId = (int) $role->id;
        if ($roleId <= 0) {
            return;
        }

        $defaults = DefaultPermissions::forRoleSlug($slug);
        if ($defaults === []) {
            return;
        }

        RolePermission::query()->where('role_id', $roleId)->delete();

        foreach ($defaults as $permission) {
            RolePermission::query()->updateOrInsert(
                [
                    'role_id' => $roleId,
                    'permission_key' => (string) $permission,
                ],
                [
                    'is_allowed' => 1,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }

    private function assertDefaultPermissionsSynced(): void
    {
        $slugs = ['super-admin', 'admin', 'editor', 'viewer'];

        foreach ($slugs as $slug) {
            $role = Role::query()->select('id')->where('slug', $slug)->first();
            if ($role === null || !isset($role->id)) {
                throw new \RuntimeException("Default role missing: {$slug}");
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
                throw new \RuntimeException("Permission seed mismatch for role: {$slug}");
            }
        }
    }
}
