<?php

declare(strict_types=1);

$safeToken = htmlspecialchars((string) ($token ?? ''), ENT_QUOTES, 'UTF-8');
?>
<div class="row row-cards">
  <div class="col-12 col-md-3">
    <div class="card">
      <div class="card-body">
        <div class="subheader">Overall</div>
        <div class="h2 mb-0 mt-1" id="monitor-overall">Loading...</div>
      </div>
    </div>
  </div>
  <div class="col-12 col-md-3">
    <div class="card">
      <div class="card-body">
        <div class="subheader">Database</div>
        <div class="h2 mb-0 mt-1" id="monitor-db">Loading...</div>
      </div>
    </div>
  </div>
  <div class="col-12 col-md-3">
    <div class="card">
      <div class="card-body">
        <div class="subheader">Cache</div>
        <div class="h2 mb-0 mt-1" id="monitor-cache">Loading...</div>
      </div>
    </div>
  </div>
  <div class="col-12 col-md-3">
    <div class="card">
      <div class="card-body">
        <div class="subheader">Runtime</div>
        <div class="h2 mb-0 mt-1" id="monitor-runtime">Loading...</div>
      </div>
    </div>
  </div>

  <div class="col-12 col-lg-8">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Health / Metrics / Info</h3>
      </div>
      <div class="card-body">
        <div class="btn-list mb-3">
          <button type="button" class="btn btn-primary" data-monitor-action="health">Health</button>
          <button type="button" class="btn btn-1" data-monitor-action="metrics">Metrics</button>
          <button type="button" class="btn btn-1" data-monitor-action="info">Info</button>
          <button type="button" class="btn btn-1" data-monitor-action="routes">Routes</button>
          <button type="button" class="btn btn-1" data-monitor-action="logs">Logs</button>
        </div>
        <pre class="p-3 rounded border mb-0" id="monitor-output">Henüz veri yüklenmedi.</pre>
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
</div>

<script>
(() => {
  const token = "<?= $safeToken ?>";
  const output = document.getElementById('monitor-output');
  const overallEl = document.getElementById('monitor-overall');
  const dbEl = document.getElementById('monitor-db');
  const cacheEl = document.getElementById('monitor-cache');
  const runtimeEl = document.getElementById('monitor-runtime');

  const withToken = (path) => {
    if (!token) return path;
    return path + (path.includes('?') ? '&' : '?') + 'token=' + encodeURIComponent(token);
  };

  const setOutput = (payload) => {
    output.textContent = JSON.stringify(payload, null, 2);
  };

  const setTextAndTone = (el, text) => {
    el.classList.remove('text-green', 'text-yellow', 'text-red');
    const value = String(text || '-');
    const normalized = value.toLowerCase();
    if (normalized.includes('healthy') || normalized.includes('up') || normalized.includes('connected')) {
      el.classList.add('text-green');
    } else if (normalized.includes('warning')) {
      el.classList.add('text-yellow');
    } else if (normalized.includes('critical') || normalized.includes('down') || normalized.includes('disconnected')) {
      el.classList.add('text-red');
    }
    el.textContent = value;
  };

  const fetchJson = async (path) => {
    const response = await fetch(withToken(path), {
      headers: token ? { 'X-Monitor-Token': token } : {},
    });
    const data = await response.json();
    if (!response.ok) {
      throw new Error(data.error || ('HTTP ' + response.status));
    }
    return data;
  };

  const loadHealthCards = async () => {
    try {
      const data = await fetchJson('/kirpi-monitor/api/health');
      setTextAndTone(overallEl, data.status || 'unknown');
      setTextAndTone(dbEl, data.checks?.database?.status || 'unknown');
      setTextAndTone(cacheEl, data.checks?.cache?.status || 'unknown');
      setTextAndTone(runtimeEl, data.uptime || 'N/A');
      setOutput(data);
    } catch (error) {
      const message = error instanceof Error ? error.message : 'Unknown error';
      setTextAndTone(overallEl, 'error');
      setTextAndTone(dbEl, 'error');
      setTextAndTone(cacheEl, 'error');
      setTextAndTone(runtimeEl, 'error');
      setOutput({ error: message });
    }
  };

  const handlers = {
    health: () => fetchJson('/kirpi-monitor/api/health'),
    metrics: () => fetchJson('/kirpi-monitor/api/metrics'),
    info: () => fetchJson('/kirpi-monitor/api/info'),
    routes: () => fetchJson('/kirpi-monitor/api/routes'),
    logs: () => fetchJson('/kirpi-monitor/api/logs?lines=80'),
  };

  document.querySelectorAll('[data-monitor-action]').forEach((button) => {
    button.addEventListener('click', async () => {
      const action = button.getAttribute('data-monitor-action');
      if (!action || !handlers[action]) return;
      try {
        const data = await handlers[action]();
        setOutput(data);
      } catch (error) {
        const message = error instanceof Error ? error.message : 'Unknown error';
        setOutput({ error: message });
      }
    });
  });

  const refreshButton = document.getElementById('monitor-refresh');
  if (refreshButton) {
    refreshButton.addEventListener('click', () => {
      loadHealthCards();
    });
  }

  loadHealthCards();
})();
</script>
