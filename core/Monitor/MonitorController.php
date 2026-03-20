<?php

declare(strict_types=1);

namespace Core\Monitor;

use Core\Http\Request;
use Core\Http\Response;
use Core\Routing\Router;

class MonitorController
{
    public function __construct(
        private readonly HealthChecker    $health,
        private readonly MetricsCollector $metrics,
    ) {}

    // ─── Dashboard HTML ──────────────────────────────────────

    public function dashboard(Request $request): Response
    {
        if (!$this->isAuthorized($request)) {
            return $this->unauthorized();
        }

        $html = file_get_contents(BASE_PATH . '/core/Monitor/dashboard.html');

        return Response::make($html, 200, [
            'Content-Type' => 'text/html; charset=utf-8',
        ]);
    }

    // ─── API Endpoints ───────────────────────────────────────

    public function health(Request $request): Response
    {
        if (!$this->isAuthorized($request)) {
            return Response::json(['error' => 'Unauthorized'], 401);
        }

        return Response::json($this->health->check());
    }

    public function metrics(Request $request): Response
    {
        if (!$this->isAuthorized($request)) {
            return Response::json(['error' => 'Unauthorized'], 401);
        }

        return Response::json($this->metrics->collect());
    }

    public function logs(Request $request): Response
    {
        if (!$this->isAuthorized($request)) {
            return Response::json(['error' => 'Unauthorized'], 401);
        }

        $lines  = (int) $request->get('lines', 50);
        $level  = $request->get('level', '');
        $logs   = $this->getRecentLogs($lines, $level);

        return Response::json(['logs' => $logs]);
    }

    public function routes(Request $request): Response
    {
        if (!$this->isAuthorized($request)) {
            return Response::json(['error' => 'Unauthorized'], 401);
        }

        $router     = app(Router::class);
        $collection = $router->getRoutes()->all();

        $routes = array_map(fn($route) => [
            'methods'     => $route->getMethods(),
            'uri'         => $route->getUri(),
            'name'        => $route->getName(),
            'middlewares' => $route->getMiddlewares(),
        ], $collection);

        return Response::json([
            'total'  => count($routes),
            'routes' => $routes,
        ]);
    }

    public function info(Request $request): Response
    {
        if (!$this->isAuthorized($request)) {
            return Response::json(['error' => 'Unauthorized'], 401);
        }

        return Response::json([
            'framework' => '🦔 Kirpi Framework',
            'version'   => config('app.version', '1.0.0'),
            'php'       => PHP_VERSION,
            'env'       => config('app.env', 'local'),
            'debug'     => config('app.debug', false),
            'locale'    => config('app.locale', 'tr'),
            'timezone'  => config('app.timezone', 'Europe/Istanbul'),
            'uptime'    => $this->getUptime(),
        ]);
    }

    // ─── Auth ────────────────────────────────────────────────

    private function isAuthorized(Request $request): bool
    {
        // Monitor devre dışı mı?
        if (!env('MONITOR_ENABLED', true)) {
            return false;
        }

        // IP whitelist
        $whitelist = env('MONITOR_IP_WHITELIST', '');
        if (!empty($whitelist)) {
            $allowed = array_map('trim', explode(',', $whitelist));
            if (!in_array($request->ip(), $allowed)) {
                return false;
            }
        }

        // Password kontrolü
        $password = env('MONITOR_PASSWORD', '');
        if (!empty($password)) {
            $token = $request->get('token')
                ?? $request->header('X-Monitor-Token')
                ?? '';

            return hash_equals($password, $token);
        }

        return true;
    }

    private function unauthorized(): Response
    {
        return Response::make(
            '<h1>401 Unauthorized</h1><p>Monitor access denied.</p>',
            401,
            ['Content-Type' => 'text/html']
        );
    }

    // ─── Helpers ─────────────────────────────────────────────

    private function getRecentLogs(int $lines, string $level): array
{
    $logFiles = glob(storage_path('logs/*-app.log'));

    if (empty($logFiles)) return [];

    $logFile = end($logFiles);
    $content = file_get_contents($logFile);
    $allLines = array_filter(explode("\n", $content));
    $recentLines = array_slice(array_values($allLines), -$lines);

    $parsed = [];
    foreach ($recentLines as $line) {
        if (preg_match('/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] (\w+)\.(\w+): (.+)/', $line, $m)) {
            if (!empty($level) && strtolower($m[3]) !== strtolower($level)) continue;

            $parsed[] = [
                'time'    => $m[1],
                'channel' => $m[2],
                'level'   => $m[3],
                'message' => $m[4],
            ];
        }
    }

    return array_reverse($parsed);
}

    private function getUptime(): string
    {
        if (file_exists('/proc/uptime')) {
            $uptime  = (int) file_get_contents('/proc/uptime');
            $days    = floor($uptime / 86400);
            $hours   = floor(($uptime % 86400) / 3600);
            $minutes = floor(($uptime % 3600) / 60);
            return "{$days}d {$hours}h {$minutes}m";
        }
        return 'N/A';
    }
}