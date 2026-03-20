<?php

declare(strict_types=1);

namespace Core\Http\Middleware;

use Core\Auth\Facades\Auth;
use Core\Http\Request;
use Core\Http\Response;

class RedirectIfAuthenticated
{
    public function handle(Request $request, \Closure $next, string $guard = 'session'): Response
    {
        if (Auth::guard($guard)->check()) {
            if ($request->expectsJson()) {
                return Response::json([
                    'error'  => 'Already authenticated.',
                    'status' => 400,
                ], 400);
            }

            return Response::redirect('/dashboard');
        }

        return $next($request);
    }
}