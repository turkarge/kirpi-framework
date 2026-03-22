<?php

declare(strict_types=1);

namespace Manager\Http\Middleware;

use Core\Http\Request;
use Core\Http\Response;
use Core\Logging\Logger;

class RequireManagerToken
{
    public function handle(Request $request, \Closure $next): Response
    {
        $logger = (new Logger())->channel('manager-audit');
        $requestIp = (string) $request->ip();

        $whitelistRaw = trim((string) env('KIRPI_MANAGER_IP_WHITELIST', ''));
        if ($whitelistRaw !== '') {
            $allowedIps = array_values(array_filter(array_map('trim', explode(',', $whitelistRaw))));
            if (!in_array($requestIp, $allowedIps, true)) {
                $logger->warning('manager.request.blocked.ip', [
                    'ip' => $requestIp,
                    'uri' => $request->uri(),
                ]);

                return Response::json([
                    'ok' => false,
                    'error' => 'IP is not allowed for manager access.',
                ], 403);
            }
        }

        $expected = trim((string) env('KIRPI_MANAGER_TOKEN', ''));
        if ($expected === '') {
            $logger->error('manager.request.blocked.missing_token_config', [
                'ip' => $requestIp,
                'uri' => $request->uri(),
            ]);

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
            $logger->warning('manager.request.blocked.bad_token', [
                'ip' => $requestIp,
                'uri' => $request->uri(),
            ]);

            return Response::json([
                'ok' => false,
                'error' => 'Unauthorized manager request.',
            ], 401);
        }

        $logger->info('manager.request.allowed', [
            'ip' => $requestIp,
            'uri' => $request->uri(),
            'method' => $request->method(),
        ]);

        return $next($request);
    }
}
