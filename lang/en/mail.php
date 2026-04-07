<?php

declare(strict_types=1);

return [
    'welcome' => [
        'subject' => 'Welcome to Kirpi Framework!',
        'greeting' => 'Hello :name,',
        'body'     => 'Welcome to Kirpi Framework! You have successfully registered.',
        'footer'   => 'This email was sent by Kirpi Framework.',
    ],
    'password_reset' => [
        'subject' => ':app password reset',
        'greeting' => 'Hello,',
        'intro' => 'We received a password reset request for your :app account.',
        'action' => 'Reset password',
        'expiry' => 'This link is valid for :minutes minutes.',
        'ignore' => 'If you did not request this, you can ignore this email.',
    ],
];
