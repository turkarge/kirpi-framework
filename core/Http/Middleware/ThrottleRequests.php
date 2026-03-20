<?php

declare(strict_types=1);

namespace Core\Http\Middleware;

use Core\Auth\Facades\Auth;
use Core\Http\Request;
use Core\Http\Response;

class ThrottleRequests
{
    public function handle(
    Request  $request,
    \Closure $next,
    int|string $maxAttempts  = 60,
    int|string $decaySeconds = 60,
): Response {
    $maxAttempts  = (int) $maxAttempts;
    $decaySeconds = (int) $decaySeconds;

    $key      = $this->resolveKey($request);
    $attempts = $this->getAttempts($key);

    if ($attempts >= $maxAttempts) {
        $retryAfter = $this->getRetryAfter($key);

        return Response::json([
            'error'       => 'Too Many Requests.',
            'status'      => 429,
            'retry_after' => $retryAfter,
        ], 429)
        ->header('Retry-After',           (string) $retryAfter)
        ->header('X-RateLimit-Limit',     (string) $maxAttempts)
        ->header('X-RateLimit-Remaining', '0');
    }

    $this->incrementAttempts($key, $decaySeconds);

    $response = $next($request);

    return $response
        ->header('X-RateLimit-Limit',     (string) $maxAttempts)
        ->header('X-RateLimit-Remaining', (string) max(0, $maxAttempts - $attempts - 1));
}

    private function resolveKey(Request $request): string
    {
        $userId = Auth::id() ?? $request->ip();
        return 'throttle:' . md5($userId . '|' . $request->path());
    }

    private function getAttempts(string $key): int
    {
        $data = $_SESSION[$key] ?? null;

        if ($data === null) return 0;

        // Decay süresi geçtiyse sıfırla
        if ($data['expires_at'] < time()) {
            unset($_SESSION[$key]);
            return 0;
        }

        return $data['attempts'];
    }

    private function incrementAttempts(string $key, int $decaySeconds): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION[$key]) || $_SESSION[$key]['expires_at'] < time()) {
            $_SESSION[$key] = [
                'attempts'   => 0,
                'expires_at' => time() + $decaySeconds,
            ];
        }

        $_SESSION[$key]['attempts']++;
    }

    private function getRetryAfter(string $key): int
    {
        $data = $_SESSION[$key] ?? null;

        if ($data === null) return 0;

        return max(0, $data['expires_at'] - time());
    }
}