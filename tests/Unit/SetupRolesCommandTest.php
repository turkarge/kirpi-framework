<?php

declare(strict_types=1);

namespace Tests\Unit;

use Core\Console\Commands\SetupRolesCommand;
use Modules\Roles\Models\Role;
use Modules\Roles\Models\RolePermission;
use Modules\Roles\Support\DefaultPermissions;
use Tests\Support\TestCase;

final class SetupRolesCommandTest extends TestCase
{
    public function test_setup_roles_seeds_default_permissions_exactly(): void
    {
        $command = new SetupRolesCommand();
        $command->setInput(['framework', 'setup:roles']);

        $exitCode = $command->handle();
        $this->assertSame(0, $exitCode);

        $slugs = ['super-admin', 'admin', 'editor', 'viewer'];
        foreach ($slugs as $slug) {
            $role = Role::query()->select('id')->where('slug', $slug)->first();
            $this->assertNotNull($role);

            $roleId = (int) ($role->id ?? 0);
            $this->assertGreaterThan(0, $roleId);

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

            $expected = DefaultPermissions::forRoleSlug($slug);
            sort($expected);

            $this->assertSame($expected, $actual, "Permission seed mismatch for {$slug}");
        }
    }
}

