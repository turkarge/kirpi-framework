<?php

declare(strict_types=1);

return [
    'aliases' => [
        'auth'       => \Core\Http\Middleware\Authenticate::class,
        'guest'      => \Core\Http\Middleware\RedirectIfAuthenticated::class,
        'permission' => \Core\Http\Middleware\CheckPermission::class,
        'csrf'       => \Core\Http\Middleware\VerifyCsrfToken::class,
        'throttle'   => \Core\Http\Middleware\ThrottleRequests::class,
        'cors'       => \Core\Http\Middleware\HandleCors::class,
    ],

    'global' => [],

    'groups' => [
        'web' => [
            'csrf',
            'throttle:120,60',
        ],
        'api' => [
            'cors',
            'throttle:60,60',
        ],
    ],
];