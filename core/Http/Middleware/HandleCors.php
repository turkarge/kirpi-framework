<?php

declare(strict_types=1);

namespace Core\Http\Middleware;

use Core\Http\Request;
use Core\Http\Response;

class HandleCors
{
    public function handle(Request $request, \Closure $next): Response
    {
        // OPTIONS preflight
        if ($request->method() === 'OPTIONS') {
            return Response::make('', 204)
                ->header('Access-Control-Allow-Origin',  env('CORS_ORIGIN', '*'))
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-CSRF-TOKEN, X-Requested-With')
                ->header('Access-Control-Max-Age',       '86400');
        }

        $response = $next($request);

        return $response
            ->header('Access-Control-Allow-Origin',  env('CORS_ORIGIN', '*'))
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-CSRF-TOKEN');
    }
}