<?php

declare(strict_types=1);

/** @var \Core\Routing\Router $router */

$router->get('/', function (\Core\Http\Request $request) {
    return \Core\Http\Response::json([
        'framework' => '🦔 Kirpi Framework',
        'version'   => '1.0.0',
        'php'       => PHP_VERSION,
        'env'       => env('APP_ENV', 'local'),
        'status'    => 'running',
        'time'      => round((microtime(true) - KIRPI_START) * 1000, 2) . 'ms',
    ]);
});

$router->get('/health', function (\Core\Http\Request $request) {
    return \Core\Http\Response::json([
        'status'  => 'healthy',
        'timestamp' => date('Y-m-d H:i:s'),
    ]);
});

$router->get('/kirpi', function (\Core\Http\Request $request) {
    $monitoring = (bool) env('KIRPI_FEATURE_MONITORING', true);
    $communication = (bool) env('KIRPI_FEATURE_COMMUNICATION', true);
    $monitoringLabel = $monitoring ? 'enabled' : 'disabled';
    $communicationLabel = $communication ? 'enabled' : 'disabled';
    $phpVersion = htmlspecialchars(PHP_VERSION, ENT_QUOTES, 'UTF-8');
    $appEnv = htmlspecialchars((string) env('APP_ENV', 'local'), ENT_QUOTES, 'UTF-8');
    $appVersion = htmlspecialchars((string) config('app.version', '1.0.0'), ENT_QUOTES, 'UTF-8');
    $gitHash = htmlspecialchars((string) env('KIRPI_GIT_HASH', 'dev'), ENT_QUOTES, 'UTF-8');

    $monitorLink = $monitoring
        ? '<a class="card" href="/kirpi-monitor"><h3>Monitor</h3><p>Health, metrics ve route gözlemi</p></a>'
        : '<div class="card disabled"><h3>Monitor</h3><p>KIRPI_FEATURE_MONITORING=false</p></div>';

    $html = <<<HTML
<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kirpi Runtime</title>
    <style>
        :root { --bg:#f5f7f2; --ink:#122215; --muted:#516056; --accent:#1f6b47; --card:#ffffff; --line:#d9e2d7; }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: "Trebuchet MS", "Segoe UI", sans-serif; background: radial-gradient(circle at 15% 10%, #e3efe2, var(--bg) 45%); color: var(--ink); }
        .wrap { max-width: 920px; margin: 0 auto; padding: 48px 20px; }
        h1 { margin: 0 0 8px; font-size: 42px; letter-spacing: -0.02em; }
        .sub { margin: 0 0 28px; color: var(--muted); }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 14px; }
        .card { display: block; text-decoration: none; color: inherit; border: 1px solid var(--line); background: var(--card); border-radius: 12px; padding: 16px; transition: transform .12s ease, border-color .12s ease; }
        .card:hover { transform: translateY(-2px); border-color: var(--accent); }
        .card h3 { margin: 0 0 8px; font-size: 18px; }
        .card p { margin: 0; color: var(--muted); font-size: 14px; }
        .disabled { opacity: .65; }
        .meta { margin-top: 18px; font-size: 14px; color: var(--muted); }
        .pill { display: inline-block; padding: 4px 8px; border: 1px solid var(--line); border-radius: 999px; margin-right: 6px; background: #fff; }
    </style>
</head>
<body>
    <main class="wrap">
        <h1>Kirpi Runtime</h1>
        <p class="sub">Tarayıcıdan hızlı kontrol paneli. Çekirdek sağlık ve feature durumlarını gösterir.</p>
        <div class="grid">
            <a class="card" href="/"><h3>API Root</h3><p>Framework canlılık JSON çıktısı</p></a>
            <a class="card" href="/health"><h3>Health</h3><p>Basit health endpoint</p></a>
            {$monitorLink}
        </div>
        <p class="meta">
            <span class="pill">ENV: {$appEnv}</span>
            <span class="pill">Version: {$appVersion}</span>
            <span class="pill">Git: {$gitHash}</span>
            <span class="pill">Monitoring: {$monitoringLabel}</span>
            <span class="pill">Communication: {$communicationLabel}</span>
            <span class="pill">PHP: {$phpVersion}</span>
        </p>
    </main>
</body>
</html>
HTML;

    return \Core\Http\Response::make($html, 200, ['Content-Type' => 'text/html; charset=utf-8']);
});

if ((bool) env('KIRPI_FEATURE_MONITORING', true)) {
    $router->group(['prefix' => '/kirpi-monitor'], function (\Core\Routing\Router $router) {
        $router->get('/', [\Core\Monitor\MonitorController::class, 'dashboard']);
        $router->get('/api/health', [\Core\Monitor\MonitorController::class, 'health']);
        $router->get('/api/metrics', [\Core\Monitor\MonitorController::class, 'metrics']);
        $router->get('/api/logs', [\Core\Monitor\MonitorController::class, 'logs']);
        $router->get('/api/routes', [\Core\Monitor\MonitorController::class, 'routes']);
        $router->get('/api/info', [\Core\Monitor\MonitorController::class, 'info']);
    });
}
