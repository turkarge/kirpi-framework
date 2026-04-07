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
$router->get('/forgot-password', [\Core\Auth\WebAuthController::class, 'showForgotPassword']);
$router->post('/forgot-password', [\Core\Auth\WebAuthController::class, 'forgotPassword']);
$router->post('/forgot-password/reset', [\Core\Auth\WebAuthController::class, 'resetForgotPassword']);
$router->get('/forgot-pin', [\Core\Auth\WebAuthController::class, 'showForgotPin'])->middleware('auth');
$router->post('/forgot-pin', [\Core\Auth\WebAuthController::class, 'forgotPin'])->middleware('auth');
$router->post('/forgot-pin/reset', [\Core\Auth\WebAuthController::class, 'resetForgotPin'])->middleware('auth');
$router->get('/tos', [\Core\Auth\WebAuthController::class, 'termsOfService']);
$router->get('/lock', [\Core\Auth\WebAuthController::class, 'showLockScreen'])->middleware('auth');
$router->post('/lock', [\Core\Auth\WebAuthController::class, 'unlock'])->middleware('auth');
$router->get('/exit', [\Core\Auth\WebAuthController::class, 'logout']);
$router->post('/exit', [\Core\Auth\WebAuthController::class, 'logout']);

// Backward compatibility routes.
$router->get('/terms-of-service', [\Core\Auth\WebAuthController::class, 'termsOfService']);
$router->get('/lock-screen', [\Core\Auth\WebAuthController::class, 'showLockScreen'])->middleware('auth');
$router->post('/lock-screen', [\Core\Auth\WebAuthController::class, 'unlock'])->middleware('auth');
$router->post('/logout', [\Core\Auth\WebAuthController::class, 'logout']);

foreach (glob(base_path('modules/*/routes/web.php')) ?: [] as $moduleRouteFile) {
    /** @var string $moduleRouteFile */
    require $moduleRouteFile;
}
