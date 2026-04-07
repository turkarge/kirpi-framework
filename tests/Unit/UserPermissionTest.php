<?php

declare(strict_types=1);

namespace Tests\Unit;

use Modules\Roles\Models\Role;
use Modules\Roles\Models\RolePermission;
use Modules\Users\Models\User;
use Tests\Support\TestCase;

final class UserPermissionTest extends TestCase
{
    public function test_super_admin_has_all_permissions(): void
    {
        $role = Role::create([
            'name' => 'Super Admin Test',
            'slug' => 'super-admin',
            'description' => 'test',
            'is_active' => 1,
            'is_system' => 1,
            'sort_order' => 1,
        ]);

        $user = User::create([
            'name' => 'SA',
            'email' => 'sa-test@example.com',
            'password' => 'secret123',
            'role_id' => (int) $role->id,
            'is_active' => 1,
        ]);

        $this->assertTrue($user->can('roles.matrix'));
        $this->assertTrue($user->can('locales.update'));
        $this->assertTrue($user->can('admin-access'));
    }

    public function test_admin_falls_back_to_default_permissions_without_matrix_records(): void
    {
        $role = Role::create([
            'name' => 'Admin Test',
            'slug' => 'admin',
            'description' => 'test',
            'is_active' => 1,
            'is_system' => 1,
            'sort_order' => 10,
        ]);

        $user = User::create([
            'name' => 'Admin',
            'email' => 'admin-test@example.com',
            'password' => 'secret123',
            'role_id' => (int) $role->id,
            'is_active' => 1,
        ]);

        $this->assertTrue($user->can('roles.matrix'));
        $this->assertTrue($user->can('users.create'));
        $this->assertTrue($user->can('admin-access'));
    }

    public function test_explicit_matrix_permissions_are_respected(): void
    {
        $role = Role::create([
            'name' => 'Editor Test',
            'slug' => 'editor-x',
            'description' => 'test',
            'is_active' => 1,
            'is_system' => 0,
            'sort_order' => 20,
        ]);

        RolePermission::create([
            'role_id' => (int) $role->id,
            'permission_key' => 'users.view',
            'is_allowed' => 1,
        ]);

        $user = User::create([
            'name' => 'Editor',
            'email' => 'editor-test@example.com',
            'password' => 'secret123',
            'role_id' => (int) $role->id,
            'is_active' => 1,
        ]);

        $this->assertTrue($user->can('users.view'));
        $this->assertFalse($user->can('roles.matrix'));
    }
}
