<?php

declare(strict_types=1);

namespace Core\Console\Commands;

use Core\Console\Command;
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
            /** @var User|null $existing */
            $existing = User::where('email', $email)->first();

            if ($existing instanceof User) {
                $existing->update([
                    'name' => $name,
                    'password' => $password,
                    'is_active' => 1,
                ]);

                $this->success("Admin user updated: {$email}");
                return 0;
            }

            User::create([
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'is_active' => 1,
            ]);

            $this->success("Admin user created: {$email}");
            return 0;
        } catch (\Throwable $e) {
            $this->error('Failed to create/update admin user: ' . $e->getMessage());
            return 1;
        }
    }
}

