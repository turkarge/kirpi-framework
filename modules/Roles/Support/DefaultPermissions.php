<?php

declare(strict_types=1);

namespace Modules\Roles\Support;

final class DefaultPermissions
{
    /**
     * @return array<int,string>
     */
    public static function all(): array
    {
        return [
            'admin-access',
            'dashboard.view',
            'users.view',
            'users.create',
            'users.update',
            'users.toggle',
            'roles.view',
            'roles.create',
            'roles.update',
            'roles.toggle',
            'roles.matrix',
            'locales.view',
            'locales.update',
            'logs.view',
        ];
    }

    /**
     * @return array<int,string>
     */
    public static function forRoleSlug(string $slug): array
    {
        $slug = strtolower(trim($slug));

        return match ($slug) {
            'super-admin' => self::all(),
            'admin' => self::all(),
            'editor' => [
                'dashboard.view',
                'users.view',
                'users.update',
                'roles.view',
                'locales.view',
                'locales.update',
                'logs.view',
            ],
            'viewer' => [
                'dashboard.view',
                'users.view',
                'roles.view',
                'locales.view',
                'logs.view',
            ],
            default => [],
        };
    }
}
