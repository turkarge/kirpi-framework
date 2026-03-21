<?php

declare(strict_types=1);

namespace Core\Support;

use Core\Container\Container;

abstract class ServiceProvider
{
    public function __construct(
        protected Container $app,
    ) {}

    public function register(): void
    {
    }

    public function boot(): void
    {
    }
}