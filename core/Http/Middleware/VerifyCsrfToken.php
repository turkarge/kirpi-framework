<?php

declare(strict_types=1);

namespace Core\Http\Middleware;

use Core\Http\Request;
use Core\Http\Response;

class VerifyCsrfToken
{
    private array $except = [
        '/api/*',
        '/webhooks/*',
    ];

    public function handle(Request $request, \Closure $next): Response
    {
        $this->startSessionIfNeeded();

        if ($this->shouldSkip($request) || $this->tokensMatch($request)) {
            return $this->addTokenToSession($next($request));
        }

        return Response::json([
            'error'  => 'CSRF token mismatch.',
            'status' => 419,
        ], 419);
    }

    private function tokensMatch(Request $request): bool
    {
        // GET, HEAD, OPTIONS — doğrulama gerekmez
        if (in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'])) {
            return true;
        }

        $token = $request->input('_token')
            ?? $request->header('X-CSRF-TOKEN')
            ?? null;

        $sessionToken = $_SESSION['_token'] ?? null;

        if ($token === null || $sessionToken === null) {
            return false;
        }

        return hash_equals($sessionToken, $token);
    }

    private function shouldSkip(Request $request): bool
    {
        foreach ($this->except as $pattern) {
            if (fnmatch($pattern, $request->path())) {
                return true;
            }
        }

        return false;
    }

    private function addTokenToSession(Response $response): Response
    {
        $this->startSessionIfNeeded();

        if (!isset($_SESSION['_token'])) {
            $_SESSION['_token'] = bin2hex(random_bytes(32));
        }

        return $response;
    }

    private function startSessionIfNeeded(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
}
