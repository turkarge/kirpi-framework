<?php

declare(strict_types=1);

namespace Core\Event;

abstract class Listener
{
    abstract public function handle(Event $event): void;

    public function shouldQueue(): bool
    {
        return false;
    }

    public function queue(): string
    {
        return 'default';
    }
}