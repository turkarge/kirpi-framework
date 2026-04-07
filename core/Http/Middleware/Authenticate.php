<?php

declare(strict_types=1);

namespace Core\Http\Middleware;

use Core\Auth\Facades\Auth;
use Core\Http\Request;
use Core\Http\Response;

class Authenticate
{
    public function handle(Request $request, \Closure $next, string $guard = 'session'): Response
    {
        $resolvedGuard = Auth::guard($guard);

        if ($resolvedGuard->guest()) {
            if ($request->expectsJson()) {
                return Response::json([
                    'error'  => 'Unauthenticated.',
                    'status' => 401,
                ], 401);
            }

            return Response::redirect('/login');
        }

        app(\Core\Auth\AuthManager::class)->shouldUse($guard);

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $isLocked = (bool) ($_SESSION['screen_locked'] ?? false);
        $path = $request->path();
        if ($isLocked && !in_array($path, ['/lock', '/lock-screen', '/exit', '/forgot-pin', '/forgot-pin/reset', '/forgot-password', '/forgot-password/reset'], true)) {
            $_SESSION['lock_return'] = $request->uri();
            return Response::redirect('/lock');
        }

        return $next($request);
    }
}
