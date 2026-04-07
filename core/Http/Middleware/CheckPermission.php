<?php

declare(strict_types=1);

namespace Core\Http\Middleware;

use Core\Auth\Facades\Auth;
use Core\Http\Request;
use Core\Http\Response;

class CheckPermission
{
    public function handle(Request $request, \Closure $next, string ...$permissions): Response
    {
        if (Auth::guest()) {
            if ($request->expectsJson()) {
                return Response::json(['error' => 'Unauthenticated.'], 401);
            }

            return Response::redirect('/login');
        }

        if ($permissions === []) {
            return $next($request);
        }

        $user = Auth::user();

        if (!method_exists($user, 'can')) {
            return Response::json([
                'error' => 'Forbidden.',
                'status' => 403,
            ], 403);
        }

        foreach ($permissions as $permission) {
            if ($user->can($permission)) {
                return $next($request);
            }
        }

        if (!$request->expectsJson()) {
            if (function_exists('flash')) {
                flash('Bu islemi yapmak icin yetkiniz yok.', 'warning', 'Yetki');
            }

            return Response::redirect('/dashboard');
        }

        return Response::json([
            'error' => 'Forbidden.',
            'status' => 403,
        ], 403);
    }
}
