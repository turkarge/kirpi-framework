<?php

declare(strict_types=1);

namespace Core\Console\Commands;

use Core\Console\Command;

class KeyGenerateCommand extends Command
{
    protected string $signature   = 'key:generate';
    protected string $description = 'Set the application key';

    public function handle(): int
    {
        $key = 'kirpi_' . bin2hex(random_bytes(32));

        $envFile = BASE_PATH . '/.env';

        if (!file_exists($envFile)) {
            $this->error('.env file not found.');
            return 1;
        }

        $content = file_get_contents($envFile);

        if (str_contains($content, 'APP_KEY=')) {
            $content = preg_replace(
                '/APP_KEY=.*/',
                "APP_KEY={$key}",
                $content
            );
        } else {
            $content .= "\nAPP_KEY={$key}";
        }

        file_put_contents($envFile, $content);

        $this->success("Application key set: {$key}");

        return 0;
    }
}