<?php

declare(strict_types=1);

/** @var \Core\Routing\Router $router */

$router->get('/', function (\Core\Http\Request $request) {
    return \Core\Http\Response::json([
        'framework' => '🦔 Kirpi Framework',
        'version'   => '1.0.0',
        'php'       => PHP_VERSION,
        'env'       => env('APP_ENV', 'local'),
        'status'    => 'running',
        'time'      => round((microtime(true) - KIRPI_START) * 1000, 2) . 'ms',
    ]);
});

$router->get('/health', function (\Core\Http\Request $request) {
    return \Core\Http\Response::json([
        'status'  => 'healthy',
        'timestamp' => date('Y-m-d H:i:s'),
    ]);
});

$router->get('/test-model', function (\Core\Http\Request $request) {
    // Kullanıcı oluştur
    $user = \Modules\Users\Models\User::create([
        'name'     => 'Test Kullanıcı',
        'email'    => 'test@kirpi.dev',
        'password' => 'secret123',
    ]);

    // Kullanıcıyı bul
    $found = \Modules\Users\Models\User::find($user->getKey());

    return \Core\Http\Response::json([
        'created' => $user->toArray(),
        'found'   => $found?->toArray(),
        'count'   => \Modules\Users\Models\User::query()->count(),
    ]);
});