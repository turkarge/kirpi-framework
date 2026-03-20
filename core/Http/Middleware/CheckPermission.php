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

        $user = Auth::user();

        // Kullanıcının can() metodu varsa kontrol et
        if (method_exists($user, 'can')) {
            foreach ($permissions as $permission) {
                if ($user->can($permission)) {
                    return $next($request);
                }
            }

            return Response::json([
                'error'  => 'Forbidden.',
                'status' => 403,
            ], 403);
        }

        return $next($request);
    }
}