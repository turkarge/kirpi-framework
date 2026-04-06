<?php

declare(strict_types=1);

/** @var \Core\Routing\Router $router */

$router->get('/', function (): \Core\Http\Response {
    if (\Core\Auth\Facades\Auth::guest()) {
        return \Core\Http\Response::redirect('/login');
    }

    return \Core\Http\Response::redirect('/dashboard');
});

$router->get('/health', function (): \Core\Http\Response {
    return \Core\Http\Response::json([
        'status' => 'healthy',
        'timestamp' => date('Y-m-d H:i:s'),
    ]);
});

$router->get('/ready', [\Core\Runtime\RuntimeController::class, 'ready']);

$router->get('/login', [\Core\Auth\WebAuthController::class, 'showLogin'])->middleware('guest');
$router->post('/login', [\Core\Auth\WebAuthController::class, 'login'])->middleware('guest');
$router->get('/forgot-password', [\Core\Auth\WebAuthController::class, 'showForgotPassword'])->middleware('guest');
$router->post('/forgot-password', [\Core\Auth\WebAuthController::class, 'forgotPassword'])->middleware('guest');
$router->get('/tos', [\Core\Auth\WebAuthController::class, 'termsOfService']);
$router->get('/dashboard', [\Core\Auth\WebAuthController::class, 'dashboard'])->middleware('auth');
$router->get('/lock', [\Core\Auth\WebAuthController::class, 'showLockScreen']);
$router->post('/lock', [\Core\Auth\WebAuthController::class, 'unlock']);
$router->get('/exit', [\Core\Auth\WebAuthController::class, 'logout']);
$router->post('/exit', [\Core\Auth\WebAuthController::class, 'logout']);

// Backward compatibility routes.
$router->get('/terms-of-service', [\Core\Auth\WebAuthController::class, 'termsOfService']);
$router->get('/lock-screen', [\Core\Auth\WebAuthController::class, 'showLockScreen']);
$router->post('/lock-screen', [\Core\Auth\WebAuthController::class, 'unlock']);
$router->post('/logout', [\Core\Auth\WebAuthController::class, 'logout']);

foreach (glob(base_path('modules/*/routes/web.php')) ?: [] as $moduleRouteFile) {
    /** @var string $moduleRouteFile */
    require $moduleRouteFile;
}
