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
        if (Auth::guard($guard)->guest()) {
            if ($request->expectsJson()) {
                return Response::json([
                    'error'  => 'Unauthenticated.',
                    'status' => 401,
                ], 401);
            }

            return Response::redirect('/login');
        }

        return $next($request);
    }
}