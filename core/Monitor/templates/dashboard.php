<?php

declare(strict_types=1);

$safeToken = htmlspecialchars((string) ($token ?? ''), ENT_QUOTES, 'UTF-8');
?>
<div class="row row-cards">
  <div class="col-12">
    <div class="card">
      <div class="card-body d-flex flex-wrap gap-2 align-items-center">
        <button type="button" class="btn btn-primary" id="monitor-load">Snapshot Yenile</button>
        <button type="button" class="btn btn-1" id="monitor-load-logs">Log Yukle</button>
        <button type="button" class="btn btn-1" id="monitor-load-routes">Route Yukle</button>
        <label class="form-check form-switch m-0 ms-md-2">
          <input class="form-check-input" type="checkbox" id="monitor-auto-refresh" checked>
          <span class="form-check-label">Auto refresh</span>
        </label>
        <select class="form-select w-auto" id="monitor-interval">
          <option value="5000">5 sn</option>
          <option value="10000" selected>10 sn</option>
          <option value="30000">30 sn</option>
          <option value="60000">60 sn</option>
        </select>
        <span class="badge bg-azure-lt" id="monitor-last-update">Son guncelleme: -</span>
        <span class="badge bg-secondary-lt" id="monitor-last-latency">Latency: -</span>
      </div>
    </div>
  </div>

  <div class="col-12 col-md-4 col-xl-2">
    <div class="card">
      <div class="card-body">
        <div class="subheader">Overall</div>
        <div class="h2 mb-0 mt-1" id="monitor-overall">-</div>
      </div>
    </div>
  </div>
  <div class="col-12 col-md-4 col-xl-2">
    <div class="card">
      <div class="card-body">
        <div class="subheader">Database</div>
        <div class="h2 mb-0 mt-1" id="monitor-db">-</div>
      </div>
    </div>
  </div>
  <div class="col-12 col-md-4 col-xl-2">
    <div class="card">
      <div class="card-body">
        <div class="subheader">Cache</div>
        <div class="h2 mb-0 mt-1" id="monitor-cache">-</div>
      </div>
    </div>
  </div>
  <div class="col-12 col-md-4 col-xl-2">
    <div class="card">
      <div class="card-body">
        <div class="subheader">Storage</div>
        <div class="h2 mb-0 mt-1" id="monitor-storage">-</div>
      </div>
    </div>
  </div>
  <div class="col-12 col-md-4 col-xl-2">
    <div class="card">
      <div class="card-body">
        <div class="subheader">Memory</div>
        <div class="h2 mb-0 mt-1" id="monitor-memory">-</div>
      </div>
    </div>
  </div>
  <div class="col-12 col-md-4 col-xl-2">
    <div class="card">
      <div class="card-body">
        <div class="subheader">Queue</div>
        <div class="h2 mb-0 mt-1" id="monitor-queue">-</div>
      </div>
    </div>
  </div>

  <div class="col-12 col-lg-8">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Kritik Metrikler</h3>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-12 col-md-6">
            <div class="border rounded p-3 h-100">
              <div class="text-secondary small">Request Today</div>
              <div class="h2 mb-0" id="metric-requests-today">-</div>
            </div>
          </div>
          <div class="col-12 col-md-6">
            <div class="border rounded p-3 h-100">
              <div class="text-secondary small">Execution (ms)</div>
              <div class="h2 mb-0" id="metric-exec-ms">-</div>
            </div>
          </div>
          <div class="col-12">
            <div class="border rounded p-3">
              <div class="d-flex justify-content-between">
                <div class="text-secondary small">Memory Kullanimi</div>
                <div class="fw-semibold" id="metric-memory">-</div>
              </div>
              <div class="progress progress-sm mt-2">
                <div class="progress-bar" id="metric-memory-bar" style="width:0%"></div>
              </div>
            </div>
          </div>
          <div class="col-12 col-md-6">
            <div class="border rounded p-3 h-100">
              <div class="text-secondary small">DB Latency</div>
              <div class="h3 mb-0" id="metric-db-latency">-</div>
            </div>
          </div>
          <div class="col-12 col-md-6">
            <div class="border rounded p-3 h-100">
              <div class="text-secondary small">CPU Load (1m / 5m / 15m)</div>
              <div class="h4 mb-0" id="metric-cpu">-</div>
            </div>
          </div>
          <div class="col-12">
            <div class="border rounded p-3">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <div class="text-secondary small">Uptime</div>
                  <div class="h3 mb-0" id="metric-uptime">-</div>
                </div>
                <span class="badge bg-green-lt">Canli</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12 col-lg-4">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Quick Links</h3>
      </div>
      <div class="card-body">
        <div class="d-grid gap-2">
          <a href="/health" class="btn btn-1">/health</a>
          <a href="/ready" class="btn btn-1">/ready</a>
          <a href="/kirpi/self-check" class="btn btn-1">/kirpi/self-check</a>
          <a href="/kirpi/admin-demo" class="btn btn-primary">Admin Demo</a>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12 col-lg-6">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h3 class="card-title">Log Stream</h3>
        <div class="d-flex gap-2">
          <select class="form-select form-select-sm" id="logs-level">
            <option value="">Tum seviyeler</option>
            <option value="debug">debug</option>
            <option value="info">info</option>
            <option value="warning">warning</option>
            <option value="error">error</option>
          </select>
          <select class="form-select form-select-sm" id="logs-lines">
            <option value="20">20 satir</option>
            <option value="50" selected>50 satir</option>
            <option value="100">100 satir</option>
            <option value="200">200 satir</option>
          </select>
        </div>
      </div>
      <div class="table-responsive">
        <table class="table table-sm table-vcenter card-table">
          <thead>
            <tr>
              <th>Saat</th>
              <th>Level</th>
              <th>Mesaj</th>
            </tr>
          </thead>
          <tbody id="monitor-logs-table">
            <tr><td colspan="3" class="text-secondary">Log verisi henuz yuklenmedi.</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="col-12 col-lg-6">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h3 class="card-title">Route Explorer</h3>
        <input type="text" class="form-control form-control-sm w-auto" id="routes-search" placeholder="Ara: /kirpi, health, api">
      </div>
      <div class="table-responsive">
        <table class="table table-sm table-vcenter card-table">
          <thead>
            <tr>
              <th>Method</th>
              <th>URI</th>
              <th>Middleware</th>
            </tr>
          </thead>
          <tbody id="monitor-routes-table">
            <tr><td colspan="3" class="text-secondary">Route verisi henuz yuklenmedi.</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Raw JSON</h3>
      </div>
      <div class="card-body">
        <pre class="p-3 rounded border mb-0" id="monitor-output">Henuz veri yuklenmedi.</pre>
      </div>
    </div>
  </div>
</div>

<script>
(() => {
  const token = "<?= $safeToken ?>";
  const output = document.getElementById('monitor-output');
  const lastUpdateEl = document.getElementById('monitor-last-update');
  const lastLatencyEl = document.getElementById('monitor-last-latency');
  const intervalEl = document.getElementById('monitor-interval');
  const autoRefreshEl = document.getElementById('monitor-auto-refresh');

  const healthCards = {
    overall: document.getElementById('monitor-overall'),
    db: document.getElementById('monitor-db'),
    cache: document.getElementById('monitor-cache'),
    storage: document.getElementById('monitor-storage'),
    memory: document.getElementById('monitor-memory'),
    queue: document.getElementById('monitor-queue'),
  };

  const metrics = {
    requestsToday: document.getElementById('metric-requests-today'),
    execMs: document.getElementById('metric-exec-ms'),
    memory: document.getElementById('metric-memory'),
    dbLatency: document.getElementById('metric-db-latency'),
    cpu: document.getElementById('metric-cpu'),
    uptime: document.getElementById('metric-uptime'),
    memoryBar: document.getElementById('metric-memory-bar'),
  };

  const logsTable = document.getElementById('monitor-logs-table');
  const logsLevel = document.getElementById('logs-level');
  const logsLines = document.getElementById('logs-lines');

  const routesTable = document.getElementById('monitor-routes-table');
  const routesSearch = document.getElementById('routes-search');

  let refreshTimer = null;
  let cachedRoutes = [];

  const withToken = (path) => {
    if (!token) return path;
    return path + (path.includes('?') ? '&' : '?') + 'token=' + encodeURIComponent(token);
  };

  const setOutput = (payload) => {
    output.textContent = JSON.stringify(payload, null, 2);
  };

  const escapeHtml = (value) => String(value ?? '')
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;');

  const setLastMeta = (startedAt) => {
    const now = new Date();
    const latency = Math.round(performance.now() - startedAt);
    lastUpdateEl.textContent = 'Son guncelleme: ' + now.toLocaleTimeString('tr-TR');
    lastLatencyEl.textContent = 'Latency: ' + latency + ' ms';
  };

  const setStatusTone = (el, value) => {
    el.classList.remove('text-green', 'text-yellow', 'text-red');
    const normalized = String(value || '-').toLowerCase();
    if (normalized.includes('healthy') || normalized.includes('up') || normalized.includes('connected')) {
      el.classList.add('text-green');
    } else if (normalized.includes('warning')) {
      el.classList.add('text-yellow');
    } else if (normalized.includes('critical') || normalized.includes('down') || normalized.includes('disconnected') || normalized.includes('error')) {
      el.classList.add('text-red');
    }
    el.textContent = String(value || '-');
  };

  const fetchJson = async (path) => {
    const startedAt = performance.now();
    const response = await fetch(withToken(path), {
      headers: token ? { 'X-Monitor-Token': token } : {},
    });
    const data = await response.json();
    if (!response.ok) {
      throw new Error(data.error || ('HTTP ' + response.status));
    }
    setLastMeta(startedAt);
    return data;
  };

  const renderLogs = (logs) => {
    if (!Array.isArray(logs) || logs.length === 0) {
      logsTable.innerHTML = '<tr><td colspan="3" class="text-secondary">Kayit bulunamadi.</td></tr>';
      return;
    }

    logsTable.innerHTML = logs.map((item) => {
      const level = String(item.level || '-');
      const badge = level === 'error'
        ? 'bg-red-lt'
        : (level === 'warning' ? 'bg-yellow-lt' : 'bg-azure-lt');
      return `<tr>
        <td>${escapeHtml(item.time || '-')}</td>
        <td><span class="badge ${badge}">${escapeHtml(level)}</span></td>
        <td>${escapeHtml(item.message || '-')}</td>
      </tr>`;
    }).join('');
  };

  const renderRoutes = (routes) => {
    if (!Array.isArray(routes) || routes.length === 0) {
      routesTable.innerHTML = '<tr><td colspan="3" class="text-secondary">Kayit bulunamadi.</td></tr>';
      return;
    }

    routesTable.innerHTML = routes.slice(0, 100).map((item) => {
      const methods = Array.isArray(item.methods) ? item.methods.join(', ') : '-';
      const middlewares = Array.isArray(item.middlewares) ? item.middlewares.join(', ') : '-';
      return `<tr>
        <td>${escapeHtml(methods)}</td>
        <td>${escapeHtml(item.uri || '-')}</td>
        <td>${escapeHtml(middlewares)}</td>
      </tr>`;
    }).join('');
  };

  const applyRouteFilter = () => {
    const term = String(routesSearch.value || '').toLowerCase().trim();
    if (!term) {
      renderRoutes(cachedRoutes);
      return;
    }
    const filtered = cachedRoutes.filter((item) => {
      const uri = String(item.uri || '').toLowerCase();
      const methods = Array.isArray(item.methods) ? item.methods.join(' ').toLowerCase() : '';
      const mws = Array.isArray(item.middlewares) ? item.middlewares.join(' ').toLowerCase() : '';
      return uri.includes(term) || methods.includes(term) || mws.includes(term);
    });
    renderRoutes(filtered);
  };

  const loadSnapshot = async () => {
    try {
      const data = await fetchJson('/kirpi-monitor/api/snapshot');
      const checks = data.health?.checks || {};
      const memoryPct = data.metrics?.memory?.pct ?? '-';
      setStatusTone(healthCards.overall, data.health?.status || 'unknown');
      setStatusTone(healthCards.db, checks.database?.status || 'unknown');
      setStatusTone(healthCards.cache, checks.cache?.status || 'unknown');
      setStatusTone(healthCards.storage, checks.storage?.status || 'unknown');
      setStatusTone(healthCards.memory, checks.memory?.status || 'unknown');
      setStatusTone(healthCards.queue, checks.queue?.status || 'unknown');

      metrics.requestsToday.textContent = String(data.metrics?.requests?.today ?? '-');
      metrics.execMs.textContent = String(data.metrics?.requests?.execution_ms ?? '-') + ' ms';
      metrics.memory.textContent = String(data.metrics?.memory?.used ?? '-') + ' / ' + String(data.metrics?.memory?.limit ?? '-') + ' (' + String(memoryPct) + '%)';
      metrics.dbLatency.textContent = String(data.metrics?.database?.latency_ms ?? '-') + ' ms';
      metrics.cpu.textContent = [
        String(data.metrics?.cpu?.['1min'] ?? '-'),
        String(data.metrics?.cpu?.['5min'] ?? '-'),
        String(data.metrics?.cpu?.['15min'] ?? '-'),
      ].join(' / ');
      metrics.uptime.textContent = String(data.health?.uptime ?? '-');
      const pct = Number(memoryPct);
      const safePct = Number.isFinite(pct) ? Math.max(0, Math.min(100, pct)) : 0;
      metrics.memoryBar.style.width = safePct + '%';
      metrics.memoryBar.classList.remove('bg-green', 'bg-yellow', 'bg-red');
      if (safePct >= 90) {
        metrics.memoryBar.classList.add('bg-red');
      } else if (safePct >= 70) {
        metrics.memoryBar.classList.add('bg-yellow');
      } else {
        metrics.memoryBar.classList.add('bg-green');
      }
      setOutput(data);
    } catch (error) {
      const message = error instanceof Error ? error.message : 'Unknown error';
      Object.values(healthCards).forEach((el) => setStatusTone(el, 'error'));
      setOutput({ error: message });
    }
  };

  const loadLogs = async () => {
    const level = String(logsLevel.value || '');
    const lines = String(logsLines.value || '50');
    const query = '/kirpi-monitor/api/logs?lines=' + encodeURIComponent(lines) + (level ? '&level=' + encodeURIComponent(level) : '');
    try {
      const data = await fetchJson(query);
      renderLogs(data.logs || []);
      setOutput(data);
    } catch (error) {
      const message = error instanceof Error ? error.message : 'Unknown error';
      renderLogs([]);
      setOutput({ error: message });
    }
  };

  const loadRoutes = async () => {
    try {
      const data = await fetchJson('/kirpi-monitor/api/routes');
      cachedRoutes = Array.isArray(data.routes) ? data.routes : [];
      applyRouteFilter();
      setOutput({ total: data.total, sample: cachedRoutes.slice(0, 20) });
    } catch (error) {
      const message = error instanceof Error ? error.message : 'Unknown error';
      cachedRoutes = [];
      renderRoutes([]);
      setOutput({ error: message });
    }
  };

  const startAutoRefresh = () => {
    if (refreshTimer !== null) {
      clearInterval(refreshTimer);
      refreshTimer = null;
    }
    if (!autoRefreshEl.checked) {
      return;
    }
    const interval = Number(intervalEl.value || 10000);
    refreshTimer = setInterval(() => {
      loadSnapshot();
    }, interval);
  };

  document.getElementById('monitor-load')?.addEventListener('click', loadSnapshot);
  document.getElementById('monitor-load-logs')?.addEventListener('click', loadLogs);
  document.getElementById('monitor-load-routes')?.addEventListener('click', loadRoutes);
  logsLevel.addEventListener('change', loadLogs);
  logsLines.addEventListener('change', loadLogs);
  routesSearch.addEventListener('input', applyRouteFilter);
  autoRefreshEl.addEventListener('change', startAutoRefresh);
  intervalEl.addEventListener('change', startAutoRefresh);

  loadSnapshot();
  loadLogs();
  loadRoutes();
  startAutoRefresh();
})();
</script>
