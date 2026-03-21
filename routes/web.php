<?php

declare(strict_types=1);

/** @var \Core\Routing\Router $router */

$runtimeChecks = static function (): array {
    $dbStartedAt = microtime(true);
    $db = ['status' => 'down', 'message' => 'unreachable', 'latency_ms' => null];

    try {
        app(\Core\Database\DatabaseManager::class)->raw('SELECT 1');
        $db = [
            'status' => 'up',
            'message' => 'ok',
            'latency_ms' => round((microtime(true) - $dbStartedAt) * 1000, 2),
        ];
    } catch (\Throwable $e) {
        $db = [
            'status' => 'down',
            'message' => $e->getMessage(),
            'latency_ms' => round((microtime(true) - $dbStartedAt) * 1000, 2),
        ];
    }

    $cacheStartedAt = microtime(true);
    $cache = ['status' => 'down', 'message' => 'unreachable', 'latency_ms' => null];

    try {
        $manager = app(\Core\Cache\CacheManager::class);
        $key = 'kirpi_runtime_check_' . bin2hex(random_bytes(4));
        $manager->set($key, 'ok', 10);
        $value = $manager->get($key);
        $manager->delete($key);

        $cache = [
            'status' => $value === 'ok' ? 'up' : 'down',
            'message' => $value === 'ok' ? 'ok' : 'read/write failed',
            'latency_ms' => round((microtime(true) - $cacheStartedAt) * 1000, 2),
        ];
    } catch (\Throwable $e) {
        $cache = [
            'status' => 'down',
            'message' => $e->getMessage(),
            'latency_ms' => round((microtime(true) - $cacheStartedAt) * 1000, 2),
        ];
    }

    return ['database' => $db, 'cache' => $cache];
};

$overallStatus = static function (array $checks): string {
    return ($checks['database']['status'] === 'up' && $checks['cache']['status'] === 'up')
        ? 'healthy'
        : 'degraded';
};

$historyPath = storage_path('framework/self-check-history.json');

$loadHistory = static function () use ($historyPath): array {
    if (!file_exists($historyPath)) {
        return [];
    }

    $raw = file_get_contents($historyPath);
    if ($raw === false || $raw === '') {
        return [];
    }

    $decoded = json_decode($raw, true);

    return is_array($decoded) ? $decoded : [];
};

$saveHistory = static function (array $history) use ($historyPath): void {
    $dir = dirname($historyPath);

    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }

    file_put_contents($historyPath, json_encode($history, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
};

$appendHistory = static function (array $entry) use ($loadHistory, $saveHistory): void {
    $history = $loadHistory();
    array_unshift($history, $entry);
    $history = array_slice($history, 0, 20);
    $saveHistory($history);
};

$router->get('/', function (): \Core\Http\Response {
    return \Core\Http\Response::json([
        'framework' => 'Kirpi Framework',
        'version' => '1.0.0',
        'php' => PHP_VERSION,
        'env' => env('APP_ENV', 'local'),
        'status' => 'running',
        'time' => round((microtime(true) - KIRPI_START) * 1000, 2) . 'ms',
    ]);
});

$router->get('/health', function (): \Core\Http\Response {
    return \Core\Http\Response::json([
        'status' => 'healthy',
        'timestamp' => date('Y-m-d H:i:s'),
    ]);
});

$router->get('/ready', function () use ($runtimeChecks, $overallStatus): \Core\Http\Response {
    $checks = $runtimeChecks();
    $status = $overallStatus($checks);
    $code = $status === 'healthy' ? 200 : 503;

    return \Core\Http\Response::json([
        'status' => $status,
        'checks' => $checks,
        'timestamp' => date('Y-m-d H:i:s'),
    ], $code);
});

$router->get('/kirpi/self-check', function () use ($runtimeChecks, $overallStatus, $appendHistory): \Core\Http\Response {
    $startedAt = microtime(true);
    $checks = $runtimeChecks();
    $status = $overallStatus($checks);

    $result = [
        'status' => $status,
        'checks' => $checks,
        'took_ms' => round((microtime(true) - $startedAt) * 1000, 2),
        'timestamp' => date('Y-m-d H:i:s'),
    ];

    $appendHistory($result);

    return \Core\Http\Response::json($result);
});

$router->get('/kirpi/self-check/history', function () use ($loadHistory): \Core\Http\Response {
    return \Core\Http\Response::json([
        'items' => $loadHistory(),
    ]);
});

$router->get('/kirpi', function () use ($runtimeChecks): \Core\Http\Response {
    $checks = $runtimeChecks();
    $monitoring = (bool) env('KIRPI_FEATURE_MONITORING', true);
    $communication = (bool) env('KIRPI_FEATURE_COMMUNICATION', true);

    $monitoringLabel = $monitoring ? 'enabled' : 'disabled';
    $communicationLabel = $communication ? 'enabled' : 'disabled';

    $phpVersion = htmlspecialchars(PHP_VERSION, ENT_QUOTES, 'UTF-8');
    $appEnv = htmlspecialchars((string) env('APP_ENV', 'local'), ENT_QUOTES, 'UTF-8');
    $appVersion = htmlspecialchars((string) config('app.version', '1.0.0'), ENT_QUOTES, 'UTF-8');
    $gitHash = htmlspecialchars((string) env('KIRPI_GIT_HASH', 'dev'), ENT_QUOTES, 'UTF-8');

    $dbStatus = $checks['database']['status'] === 'up' ? 'DB: up' : 'DB: down';
    $cacheStatus = $checks['cache']['status'] === 'up' ? 'Cache: up' : 'Cache: down';

    $dbClass = $checks['database']['status'] === 'up' ? 'ok' : 'bad';
    $cacheClass = $checks['cache']['status'] === 'up' ? 'ok' : 'bad';

    $monitorLink = $monitoring
        ? '<a class="card" href="/kirpi-monitor"><h3>Monitor</h3><p>Health, metrics ve route gozlemi</p></a>'
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
        .ok { border-color: #8dc8a3; background: #ecf8f0; color: #185234; }
        .bad { border-color: #d7a5a5; background: #fff1f1; color: #772f2f; }
        .actions { margin-top: 16px; display: flex; gap: 10px; align-items: center; }
        .btn { border: 1px solid var(--accent); background: var(--accent); color: #fff; border-radius: 10px; padding: 8px 12px; cursor: pointer; font-weight: 600; }
        .btn:hover { filter: brightness(0.95); }
        pre { margin: 12px 0 0; background: #fff; border: 1px solid var(--line); border-radius: 10px; padding: 12px; overflow: auto; font-size: 13px; }
        .history { margin-top: 18px; display: grid; gap: 10px; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); }
        .history-card { border: 1px solid var(--line); background: #fff; border-radius: 10px; padding: 10px; }
        .history-title { margin: 0 0 4px; font-size: 13px; font-weight: 700; }
        .history-meta { margin: 0; color: var(--muted); font-size: 12px; }
    </style>
</head>
<body>
    <main class="wrap">
        <h1>Kirpi Runtime</h1>
        <p class="sub">Tarayicidan hizli kontrol paneli. Cekirdek saglik ve feature durumlarini gosterir.</p>
        <div class="grid">
            <a class="card" href="/"><h3>API Root</h3><p>Framework canlilik JSON ciktisi</p></a>
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
            <span class="pill {$dbClass}">{$dbStatus}</span>
            <span class="pill {$cacheClass}">{$cacheStatus}</span>
        </p>
        <div class="actions">
            <button class="btn" id="selfCheckBtn" type="button">Run Self-Check</button>
            <button class="btn" id="historyBtn" type="button">Load History</button>
            <span id="selfCheckStatus" class="sub" style="margin:0;"></span>
        </div>
        <div id="historyCards" class="history"></div>
        <pre id="selfCheckOutput">Self-check sonucu burada gorunecek.</pre>
    </main>
    <script>
        const btn = document.getElementById('selfCheckBtn');
        const historyBtn = document.getElementById('historyBtn');
        const status = document.getElementById('selfCheckStatus');
        const out = document.getElementById('selfCheckOutput');
        const historyCards = document.getElementById('historyCards');

        function renderHistory(items) {
            const topFive = (Array.isArray(items) ? items : []).slice(0, 5);
            if (topFive.length === 0) {
                historyCards.innerHTML = '<div class="history-card"><p class="history-title">No history</p><p class="history-meta">Self-check calistirildiginda burada gorunur.</p></div>';
                return;
            }

            historyCards.innerHTML = topFive.map((item, index) => {
                const db = item?.checks?.database?.status ?? 'unknown';
                const cache = item?.checks?.cache?.status ?? 'unknown';
                const took = typeof item?.took_ms === 'number' ? item.took_ms.toFixed(2) + 'ms' : '-';
                const stamp = item?.timestamp ?? '-';
                const state = item?.status ?? 'unknown';

                return '<div class="history-card">'
                    + '<p class="history-title">#' + (index + 1) + ' ' + state + '</p>'
                    + '<p class="history-meta">DB: ' + db + ' | Cache: ' + cache + '</p>'
                    + '<p class="history-meta">Took: ' + took + '</p>'
                    + '<p class="history-meta">' + stamp + '</p>'
                    + '</div>';
            }).join('');
        }

        async function fetchHistory() {
            const res = await fetch('/kirpi/self-check/history', {headers: {'Accept': 'application/json'}});
            const data = await res.json();
            renderHistory(data.items || []);
            return data;
        }

        btn.addEventListener('click', async () => {
            btn.disabled = true;
            status.textContent = 'Checking...';
            try {
                const res = await fetch('/kirpi/self-check', {headers: {'Accept': 'application/json'}});
                const data = await res.json();
                status.textContent = 'Done (' + (data.status || 'unknown') + ')';
                out.textContent = JSON.stringify(data, null, 2);
                await fetchHistory();
            } catch (err) {
                status.textContent = 'Failed';
                out.textContent = String(err);
            } finally {
                btn.disabled = false;
            }
        });

        historyBtn.addEventListener('click', async () => {
            historyBtn.disabled = true;
            status.textContent = 'Loading history...';
            try {
                const data = await fetchHistory();
                status.textContent = 'History loaded';
                out.textContent = JSON.stringify(data, null, 2);
            } catch (err) {
                status.textContent = 'History failed';
                out.textContent = String(err);
            } finally {
                historyBtn.disabled = false;
            }
        });

        fetchHistory().catch(() => {});
    </script>
</body>
</html>
HTML;

    return \Core\Http\Response::make($html, 200, ['Content-Type' => 'text/html; charset=utf-8']);
});

if ((bool) env('KIRPI_FEATURE_MONITORING', true)) {
    $router->group(['prefix' => '/kirpi-monitor'], function (\Core\Routing\Router $router): void {
        $router->get('/', [\Core\Monitor\MonitorController::class, 'dashboard']);
        $router->get('/api/health', [\Core\Monitor\MonitorController::class, 'health']);
        $router->get('/api/metrics', [\Core\Monitor\MonitorController::class, 'metrics']);
        $router->get('/api/logs', [\Core\Monitor\MonitorController::class, 'logs']);
        $router->get('/api/routes', [\Core\Monitor\MonitorController::class, 'routes']);
        $router->get('/api/info', [\Core\Monitor\MonitorController::class, 'info']);
    });
}
