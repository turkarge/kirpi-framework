<?php

declare(strict_types=1);

namespace Core\Mail;

interface MailDriverInterface
{
    public function send(Mailable $mailable): bool;
    public function getDriverName(): string;
}