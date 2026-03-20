<?php

declare(strict_types=1);

namespace Core\Mail\Drivers;

use Core\Mail\MailDriverInterface;
use Core\Mail\Mailable;

class LogDriver implements MailDriverInterface
{
    public function send(Mailable $mailable): bool
    {
        $mailable->build();

        $log = [
            'from'    => $mailable->getFrom(),
            'to'      => array_column($mailable->getTo(), 'email'),
            'subject' => $mailable->getSubject(),
            'html'    => $mailable->getHtmlBody() ? substr($mailable->getHtmlBody(), 0, 100) . '...' : null,
            'text'    => $mailable->getTextBody(),
        ];

        app(\Core\Logging\Logger::class)
            ->channel('mail')
            ->info('Mail sent (log driver)', $log);

        return true;
    }

    public function getDriverName(): string
    {
        return 'log';
    }
}