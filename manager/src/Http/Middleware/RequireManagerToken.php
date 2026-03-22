<?php

declare(strict_types=1);

namespace Manager\Http\Middleware;

use Core\Http\Request;
use Core\Http\Response;

class RequireManagerToken
{
    public function handle(Request $request, \Closure $next): Response
    {
        $expected = trim((string) env('KIRPI_MANAGER_TOKEN', ''));
        if ($expected === '') {
            return Response::json([
                'ok' => false,
                'error' => 'Manager token is not configured. Set KIRPI_MANAGER_TOKEN.',
            ], 503);
        }

        $actual = trim((string) ($request->header('X-Manager-Token') ?? ''));
        if ($actual === '') {
            $actual = trim((string) $request->get('token', ''));
        }

        if (!hash_equals($expected, $actual)) {
            return Response::json([
                'ok' => false,
                'error' => 'Unauthorized manager request.',
            ], 401);
        }

        return $next($request);
    }
}

