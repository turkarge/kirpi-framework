<?php

declare(strict_types=1);

namespace Core\Console\Commands;

use Core\Console\Command;
use Modules\Roles\Models\Role;

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
            }

            $this->success('Default roles are ready.');
            return 0;
        } catch (\Throwable $e) {
            $this->error('Failed to setup roles: ' . $e->getMessage());
            return 1;
        }
    }
}
