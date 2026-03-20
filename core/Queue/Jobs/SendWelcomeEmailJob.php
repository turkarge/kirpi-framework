<?php

declare(strict_types=1);

namespace Core\Queue\Jobs;

use Core\Queue\Job;

class SendWelcomeEmailJob extends Job
{
    public string $queue = 'emails';

    public function __construct(
        private readonly string $email,
        private readonly string $name,
    ) {}

    public function handle(): void
    {
        // Mail gönderme simülasyonu
        app(\Core\Logging\Logger::class)->info(
            "Sending welcome email",
            ['email' => $this->email, 'name' => $this->name]
        );

        // Gerçek mail sistemi eklenince buraya gelecek
        // Mail::to($this->email)->send(new WelcomeMail($this->name));
    }

    public function failed(\Throwable $e): void
    {
        app(\Core\Logging\Logger::class)->error(
            "Failed to send welcome email",
            ['email' => $this->email, 'error' => $e->getMessage()]
        );
    }
}