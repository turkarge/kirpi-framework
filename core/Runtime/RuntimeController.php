<?php

declare(strict_types=1);

namespace Core\Runtime;

use Core\Http\Response;

class RuntimeController
{
    public function __construct(
        private readonly RuntimeDiagnostics $diagnostics,
    ) {}

    public function ready(): Response
    {
        $payload = $this->diagnostics->readinessPayload();
        $status = (string) ($payload['status'] ?? 'degraded');
        $code = $status === 'healthy' ? 200 : 503;

        return Response::json($payload, $code);
    }

    public function selfCheck(): Response
    {
        return Response::json($this->diagnostics->runSelfCheck());
    }

    public function selfCheckHistory(): Response
    {
        return Response::json($this->diagnostics->historyPayload());
    }

    public function dashboard(): Response
    {
        if (!$this->isDashboardEnabled()) {
            return Response::make('<h1>404 Not Found</h1>', 404, ['Content-Type' => 'text/html; charset=utf-8']);
        }

        $checks = $this->diagnostics->checks();
        $monitoring = (bool) env('KIRPI_FEATURE_MONITORING', true);
        $communication = (bool) env('KIRPI_FEATURE_COMMUNICATION', true);

        $templateData = [
            'monitoringLabel' => $monitoring ? 'enabled' : 'disabled',
            'communicationLabel' => $communication ? 'enabled' : 'disabled',
            'phpVersion' => htmlspecialchars(PHP_VERSION, ENT_QUOTES, 'UTF-8'),
            'appEnv' => htmlspecialchars((string) env('APP_ENV', 'local'), ENT_QUOTES, 'UTF-8'),
            'appVersion' => htmlspecialchars((string) config('app.version', '1.0.0'), ENT_QUOTES, 'UTF-8'),
            'gitHash' => htmlspecialchars((string) env('KIRPI_GIT_HASH', 'dev'), ENT_QUOTES, 'UTF-8'),
            'dbStatus' => $checks['database']['status'] === 'up' ? 'DB: up' : 'DB: down',
            'cacheStatus' => $checks['cache']['status'] === 'up' ? 'Cache: up' : 'Cache: down',
            'dbClass' => $checks['database']['status'] === 'up' ? 'ok' : 'bad',
            'cacheClass' => $checks['cache']['status'] === 'up' ? 'ok' : 'bad',
            'monitorLink' => $monitoring
                ? '<a class="card" href="/kirpi-monitor"><h3>Monitor</h3><p>Health, metrics ve route gozlemi</p></a>'
                : '<div class="card disabled"><h3>Monitor</h3><p>KIRPI_FEATURE_MONITORING=false</p></div>',
        ];

        return Response::make(
            $this->renderDashboard($templateData),
            200,
            ['Content-Type' => 'text/html; charset=utf-8']
        );
    }

    /** @param array<string, string> $data */
    private function renderDashboard(array $data): string
    {
        extract($data, EXTR_SKIP);

        ob_start();
        require __DIR__ . '/dashboard.php';

        return (string) ob_get_clean();
    }

    private function isDashboardEnabled(): bool
    {
        if (!(bool) env('KIRPI_RUNTIME_DASHBOARD_ENABLED', true)) {
            return false;
        }

        if ((string) env('APP_ENV', 'local') !== 'production') {
            return true;
        }

        return (bool) env('KIRPI_RUNTIME_DASHBOARD_IN_PRODUCTION', false);
    }
}
