<?php

declare(strict_types=1);

namespace Core\Console\Commands;

use Core\Console\Command;
use Modules\Roles\Models\Role;
use Modules\Users\Models\User;

class SetupAdminCommand extends Command
{
    protected string $signature = 'setup:admin';
    protected string $description = 'Create or update the initial admin user';

    public function handle(): int
    {
        $name = trim((string) $this->option('name', ''));
        $email = trim((string) $this->option('email', ''));
        $password = (string) $this->option('password', '');

        if ($name === '' || $email === '' || $password === '') {
            $this->error('Missing required options: --name, --email, --password');
            $this->line('Usage: php framework setup:admin --name=Admin --email=admin@example.com --password=secret');
            return 1;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Invalid admin email address.');
            return 1;
        }

        try {
            $defaultRoleId = $this->resolveDefaultRoleId();

            /** @var User|null $existing */
            $existing = User::where('email', $email)->first();

            if ($existing instanceof User) {
                $existing->update([
                    'name' => $name,
                    'password' => $password,
                    'is_active' => 1,
                    'role_id' => $defaultRoleId,
                ]);

                $this->success("Admin user updated: {$email}");
                return 0;
            }

            User::create([
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'is_active' => 1,
                'role_id' => $defaultRoleId,
            ]);

            $this->success("Admin user created: {$email}");
            return 0;
        } catch (\Throwable $e) {
            $this->error('Failed to create/update admin user: ' . $e->getMessage());
            return 1;
        }
    }

    private function resolveDefaultRoleId(): ?int
    {
        try {
            $prioritySlugs = ['super-admin', 'admin'];
            foreach ($prioritySlugs as $slug) {
                $role = Role::query()->select('id')->where('slug', $slug)->first();
                if ($role !== null && isset($role->id)) {
                    return (int) $role->id;
                }
            }

            $role = Role::query()
                ->select('id')
                ->where('is_active', 1)
                ->orderBy('sort_order', 'ASC')
                ->first();

            return $role !== null && isset($role->id) ? (int) $role->id : null;
        } catch (\Throwable) {
            return null;
        }
    }
}
