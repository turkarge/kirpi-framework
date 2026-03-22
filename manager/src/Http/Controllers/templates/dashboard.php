<?php
declare(strict_types=1);
/** @var string $token */
/** @var string $appEnv */
/** @var string $appUrl */
/** @var string $phpVersion */
/** @var string $currentPath */
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kirpi Manager Dashboard</title>
  <link rel="stylesheet" href="/vendor/tabler/dist/css/tabler.min.css">
  <style>
    pre { max-height: 540px; overflow: auto; margin: 0; }
    .metric-label { font-size: .7rem; text-transform: uppercase; letter-spacing: .04em; color: var(--tblr-secondary); }
  </style>
</head>
<body>
<div class="page">
  <?php require __DIR__ . '/partials/navbar.php'; ?>

  <div class="page-wrapper">
    <div class="page-header d-print-none py-2">
      <div class="container-xl">
        <div class="page-pretitle">KIRPI FRAMEWORK</div>
        <h2 class="page-title">Control Dashboard</h2>
      </div>
    </div>

    <div class="page-body py-2">
      <div class="container-xl">
        <div class="row row-cards g-2 mb-2">
          <div class="col-sm-6 col-lg-3"><div class="card"><div class="card-body py-2"><div class="metric-label">Context</div><div class="h3 mb-0" id="kpiContext">manager</div></div></div></div>
          <div class="col-sm-6 col-lg-3"><div class="card"><div class="card-body py-2"><div class="metric-label">Routes</div><div class="h3 mb-0" id="kpiRoutes">-</div></div></div></div>
          <div class="col-sm-6 col-lg-3"><div class="card"><div class="card-body py-2"><div class="metric-label">Modules</div><div class="h3 mb-0" id="kpiModules">-</div></div></div></div>
          <div class="col-sm-6 col-lg-3"><div class="card"><div class="card-body py-2"><div class="metric-label">Features</div><div class="h5 mb-0" id="kpiFeatures">-</div></div></div></div>
        </div>

        <div class="row row-cards g-2">
          <div class="col-12 col-xl-4">
            <div class="card">
              <div class="card-header py-2"><h3 class="card-title">Operations Console</h3></div>
              <div class="card-body">
                <label class="form-label mb-1">Manager Token</label>
                <input id="managerToken" class="form-control form-control-sm mb-1" type="text" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>" placeholder="KIRPI_MANAGER_TOKEN">
                <div class="form-hint mb-2">Target: <?= htmlspecialchars($appUrl, ENT_QUOTES, 'UTF-8') ?></div>
                <div class="d-grid gap-2">
                  <button class="btn btn-primary btn-sm" id="btnOverview">Overview</button>
                  <button class="btn btn-outline-primary btn-sm" id="btnModules">Modules</button>
                  <button class="btn btn-outline-primary btn-sm" id="btnEnv">Env (masked)</button>
                  <button class="btn btn-outline-primary btn-sm" id="btnRuntimeReady">Runtime Ready</button>
                  <button class="btn btn-outline-primary btn-sm" id="btnRuntimeSelfCheck">Self Check</button>
                  <button class="btn btn-outline-primary btn-sm" id="btnRuntimeHistory">History</button>
                </div>
              </div>
            </div>
          </div>

          <div class="col-12 col-xl-8">
            <div class="card">
              <div class="card-header py-2">
                <h3 class="card-title">Console</h3>
                <ul class="nav nav-tabs card-header-tabs ms-auto" data-bs-toggle="tabs">
                  <li class="nav-item"><a href="#console-output" class="nav-link active" data-bs-toggle="tab">API Output</a></li>
                  <li class="nav-item"><a href="#console-activity" class="nav-link" data-bs-toggle="tab">Activity</a></li>
                </ul>
              </div>
              <div class="card-body p-0 tab-content">
                <div class="tab-pane active" id="console-output"><pre class="p-3" id="output">No action yet.</pre></div>
                <div class="tab-pane" id="console-activity"><pre class="p-3" id="activityLog">No activity yet.</pre></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="/vendor/tabler/dist/js/tabler.min.js"></script>
<script>
(() => {
  const output = document.getElementById('output');
  const tokenInput = document.getElementById('managerToken');
  const activityLog = document.getElementById('activityLog');

  const kpiContext = document.getElementById('kpiContext');
  const kpiRoutes = document.getElementById('kpiRoutes');
  const kpiModules = document.getElementById('kpiModules');
  const kpiFeatures = document.getElementById('kpiFeatures');

  const write = (payload) => { output.textContent = JSON.stringify(payload, null, 2); };
  const appendLog = (text) => {
    const line = '[' + new Date().toLocaleTimeString() + '] ' + text;
    if (activityLog.textContent === 'No activity yet.') {
      activityLog.textContent = line;
      return;
    }
    activityLog.textContent = line + '\n' + activityLog.textContent;
  };

  const applyOverviewKpi = (payload) => {
    const data = payload?.data || {};
    kpiContext.textContent = String(data.context || '-');
    kpiRoutes.textContent = String(data.routes_total ?? '-');
    kpiModules.textContent = String(data.modules_total ?? '-');
    const features = data.features || {};
    const enabled = Object.keys(features).filter((key) => features[key] === true).length;
    kpiFeatures.textContent = enabled + '/3 enabled';
  };

  const callApi = async (url, label = '') => {
    const token = String(tokenInput.value || '').trim();
    const hasQuery = url.includes('?');
    const withToken = url + (hasQuery ? '&' : '?') + 'token=' + encodeURIComponent(token);
    const res = await fetch(withToken, {
      headers: { 'Accept': 'application/json', 'X-Manager-Token': token },
    });

    let payload;
    try {
      payload = await res.json();
    } catch (_e) {
      payload = { ok: false, error: 'Non-JSON response', status: res.status };
    }

    write(payload);
    if (label) appendLog(label + ' -> ' + (payload.ok ? 'ok' : 'fail'));
    return payload;
  };

  document.getElementById('btnOverview')?.addEventListener('click', async () => {
    const payload = await callApi('/manager/api/overview', 'Overview');
    if (payload?.ok) applyOverviewKpi(payload);
  });
  document.getElementById('btnModules')?.addEventListener('click', () => callApi('/manager/api/modules', 'Modules'));
  document.getElementById('btnEnv')?.addEventListener('click', () => callApi('/manager/api/env', 'Env'));
  document.getElementById('btnRuntimeReady')?.addEventListener('click', () => callApi('/manager/api/runtime/ready', 'Runtime Ready'));
  document.getElementById('btnRuntimeSelfCheck')?.addEventListener('click', () => callApi('/manager/api/runtime/self-check', 'Runtime Self Check'));
  document.getElementById('btnRuntimeHistory')?.addEventListener('click', () => callApi('/manager/api/runtime/history', 'Runtime History'));

  (async () => {
    const payload = await callApi('/manager/api/overview', 'Overview (autoload)');
    if (payload?.ok) applyOverviewKpi(payload);
  })();
})();
</script>
</body>
</html>
