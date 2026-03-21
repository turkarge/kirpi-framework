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
        .btn.secondary { background: #fff; color: var(--accent); }
        pre { margin: 12px 0 0; background: #fff; border: 1px solid var(--line); border-radius: 10px; padding: 12px; overflow: auto; font-size: 13px; }
        .history { margin-top: 18px; display: grid; gap: 10px; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); }
        .history-card { border: 1px solid var(--line); background: #fff; border-radius: 10px; padding: 10px; }
        .history-title { margin: 0 0 4px; font-size: 13px; font-weight: 700; }
        .history-meta { margin: 0; color: var(--muted); font-size: 12px; }
        .trend { margin-top: 10px; padding: 10px; border: 1px solid var(--line); border-radius: 10px; background: #fff; font-size: 13px; color: var(--muted); }
    </style>
</head>
<body>
    <main class="wrap">
        <h1>Kirpi Runtime</h1>
        <p class="sub">Tarayicidan hizli kontrol paneli. Cekirdek saglik ve feature durumlarini gosterir.</p>
        <div class="grid">
            <a class="card" href="/"><h3>API Root</h3><p>Framework canlilik JSON ciktisi</p></a>
            <a class="card" href="/health"><h3>Health</h3><p>Basit health endpoint</p></a>
            <?= $monitorLink ?>
        </div>
        <p class="meta">
            <span class="pill">ENV: <?= $appEnv ?></span>
            <span class="pill">Version: <?= $appVersion ?></span>
            <span class="pill">Git: <?= $gitHash ?></span>
            <span class="pill">Monitoring: <?= $monitoringLabel ?></span>
            <span class="pill">Communication: <?= $communicationLabel ?></span>
            <span class="pill">PHP: <?= $phpVersion ?></span>
            <span class="pill <?= $dbClass ?>"><?= $dbStatus ?></span>
            <span class="pill <?= $cacheClass ?>"><?= $cacheStatus ?></span>
        </p>
        <div class="actions">
            <button class="btn" id="selfCheckBtn" type="button">Run Self-Check</button>
            <button class="btn" id="historyBtn" type="button">Load History</button>
            <button class="btn secondary" id="copyDiagnosticsBtn" type="button">Copy Diagnostics</button>
            <span id="selfCheckStatus" class="sub" style="margin:0;"></span>
        </div>
        <div id="latencyTrend" class="trend">Latency trend: loading...</div>
        <div id="historyCards" class="history"></div>
        <pre id="selfCheckOutput">Self-check sonucu burada gorunecek.</pre>
    </main>
    <script>
        const btn = document.getElementById('selfCheckBtn');
        const historyBtn = document.getElementById('historyBtn');
        const copyDiagnosticsBtn = document.getElementById('copyDiagnosticsBtn');
        const status = document.getElementById('selfCheckStatus');
        const out = document.getElementById('selfCheckOutput');
        const latencyTrend = document.getElementById('latencyTrend');
        const historyCards = document.getElementById('historyCards');
        const runtimeMeta = {
            env: '<?= $appEnv ?>',
            version: '<?= $appVersion ?>',
            git: '<?= $gitHash ?>',
            php: '<?= $phpVersion ?>',
            monitoring: '<?= $monitoringLabel ?>',
            communication: '<?= $communicationLabel ?>',
        };

        let latestHistory = [];
        let latestTrend = null;
        let latestSelfCheck = null;

        function renderTrend(trend) {
            const t = trend || {};
            if (!Array.isArray(t.points) || t.points.length === 0) {
                latencyTrend.textContent = 'Latency trend: no data yet.';
                return;
            }

            const points = t.points.map(v => Number(v).toFixed(2)).join(', ');
            latencyTrend.textContent = 'Latency trend (' + t.direction + ')'
                + ' | last: ' + t.last_ms + 'ms'
                + ' | avg: ' + t.avg_ms + 'ms'
                + ' | min/max: ' + t.min_ms + '/' + t.max_ms + 'ms'
                + ' | points: [' + points + ']';
        }

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
            latestHistory = Array.isArray(data.items) ? data.items : [];
            latestTrend = data.latency_trend || null;
            renderHistory(data.items || []);
            renderTrend(data.latency_trend || null);
            return data;
        }

        btn.addEventListener('click', async () => {
            btn.disabled = true;
            status.textContent = 'Checking...';
            try {
                const res = await fetch('/kirpi/self-check', {headers: {'Accept': 'application/json'}});
                const data = await res.json();
                latestSelfCheck = data;
                status.textContent = 'Done (' + (data.status || 'unknown') + ')';
                out.textContent = JSON.stringify(data, null, 2);
                renderTrend(data.latency_trend || null);
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

        copyDiagnosticsBtn.addEventListener('click', async () => {
            copyDiagnosticsBtn.disabled = true;
            status.textContent = 'Preparing diagnostics...';

            const diagnostics = {
                captured_at: new Date().toISOString(),
                runtime: runtimeMeta,
                readiness: {
                    database: '<?= $dbStatus ?>',
                    cache: '<?= $cacheStatus ?>',
                },
                latest_self_check: latestSelfCheck,
                latency_trend: latestTrend,
                recent_history: latestHistory.slice(0, 5),
            };

            const payload = JSON.stringify(diagnostics, null, 2);

            try {
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    await navigator.clipboard.writeText(payload);
                } else {
                    const area = document.createElement('textarea');
                    area.value = payload;
                    document.body.appendChild(area);
                    area.select();
                    document.execCommand('copy');
                    document.body.removeChild(area);
                }

                status.textContent = 'Diagnostics copied';
                out.textContent = payload;
            } catch (err) {
                status.textContent = 'Copy failed';
                out.textContent = payload;
            } finally {
                copyDiagnosticsBtn.disabled = false;
            }
        });

        fetchHistory().catch(() => {});
    </script>
</body>
</html>
