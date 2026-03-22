<?php
declare(strict_types=1);
/** @var string $token */
/** @var string $appEnv */
/** @var string $appUrl */
/** @var string $phpVersion */
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kirpi Manager Control Plane</title>
  <link rel="stylesheet" href="/vendor/tabler/dist/css/tabler.min.css">
  <style>
    pre { max-height: 380px; overflow: auto; margin: 0; }
    .wizard-step { border-left: 3px solid var(--tblr-border-color); padding-left: .75rem; }
    .wizard-step.is-active { border-left-color: var(--tblr-primary); }
    .metric-label { font-size: .7rem; text-transform: uppercase; letter-spacing: .04em; color: var(--tblr-secondary); }
    .card-body-compact { padding: .75rem 1rem; }
    .list-compact .list-group-item { padding-top: .45rem; padding-bottom: .45rem; }
  </style>
</head>
<body>
<div class="page">
  <header class="navbar navbar-expand-md d-print-none">
    <div class="container-xl">
      <h1 class="navbar-brand mb-0">Kirpi Manager</h1>
      <div class="navbar-nav ms-auto">
        <span class="nav-link disabled">ENV: <?= htmlspecialchars($appEnv, ENT_QUOTES, 'UTF-8') ?></span>
        <span class="nav-link disabled">PHP: <?= htmlspecialchars($phpVersion, ENT_QUOTES, 'UTF-8') ?></span>
      </div>
    </div>
  </header>

  <div class="page-wrapper">
    <div class="page-header d-print-none py-2">
      <div class="container-xl">
        <div class="row g-2 align-items-center">
          <div class="col">
            <div class="page-pretitle">KIRPI FRAMEWORK</div>
            <h2 class="page-title">Manager Control Center</h2>
            <div class="text-secondary">Tek merkezden runtime, generation ve dev lab operasyonlari.</div>
          </div>
          <div class="col-auto">
            <a class="btn btn-primary btn-sm" href="/kirpi/admin-demo" target="_blank" rel="noreferrer">Open Dashboard</a>
          </div>
        </div>
      </div>
    </div>

    <div class="page-body py-2">
      <div class="container-xl">
        <div class="row row-cards g-2">
          <div class="col-sm-6 col-lg-3">
            <div class="card"><div class="card-body card-body-compact"><div class="metric-label">Context</div><div class="h3 mb-0" id="kpiContext">manager</div></div></div>
          </div>
          <div class="col-sm-6 col-lg-3">
            <div class="card"><div class="card-body card-body-compact"><div class="metric-label">Routes</div><div class="h3 mb-0" id="kpiRoutes">-</div></div></div>
          </div>
          <div class="col-sm-6 col-lg-3">
            <div class="card"><div class="card-body card-body-compact"><div class="metric-label">Modules</div><div class="h3 mb-0" id="kpiModules">-</div></div></div>
          </div>
          <div class="col-sm-6 col-lg-3">
            <div class="card"><div class="card-body card-body-compact"><div class="metric-label">Features</div><div class="h5 mb-0" id="kpiFeatures">-</div></div></div>
          </div>

          <div class="col-12 col-xl-8">
            <div class="card">
              <div class="card-header py-2">
                <h3 class="card-title">Operations Console</h3>
                <ul class="nav nav-tabs card-header-tabs ms-auto" data-bs-toggle="tabs">
                  <li class="nav-item"><a href="#ops-system" class="nav-link active" data-bs-toggle="tab">System APIs</a></li>
                  <li class="nav-item"><a href="#ops-wizard" class="nav-link" data-bs-toggle="tab">Module Wizard</a></li>
                  <li class="nav-item"><a href="#ops-mail" class="nav-link" data-bs-toggle="tab">Mail</a></li>
                </ul>
              </div>
              <div class="card-body card-body-compact">
                <div class="row g-2 mb-2">
                  <div class="col-12">
                    <label class="form-label mb-1">Manager Token</label>
                    <input id="managerToken" class="form-control form-control-sm" type="text" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>" placeholder="KIRPI_MANAGER_TOKEN">
                    <div class="form-hint">Target: <?= htmlspecialchars($appUrl, ENT_QUOTES, 'UTF-8') ?></div>
                  </div>
                </div>

                <div class="tab-content">
                  <div class="tab-pane active" id="ops-system">
                    <div class="text-secondary mb-2">Core ve runtime endpoint kontrolleri</div>
                    <div class="btn-list">
                      <button class="btn btn-primary btn-sm" id="btnOverview">Overview</button>
                      <button class="btn btn-outline-primary btn-sm" id="btnModules">Modules</button>
                      <button class="btn btn-outline-primary btn-sm" id="btnEnv">Env (masked)</button>
                      <button class="btn btn-outline-primary btn-sm" id="btnRuntimeReady">Runtime Ready</button>
                      <button class="btn btn-outline-primary btn-sm" id="btnRuntimeSelfCheck">Self Check</button>
                      <button class="btn btn-outline-primary btn-sm" id="btnRuntimeHistory">History</button>
                    </div>
                  </div>

                  <div class="tab-pane" id="ops-wizard">
                    <div class="row g-2">
                      <div class="col-12 col-lg-6">
                        <div class="wizard-step is-active" id="wizardStep1">
                          <div class="fw-semibold mb-1">Step 1 - Module</div>
                          <input id="moduleName" class="form-control form-control-sm" placeholder="Catalog">
                        </div>
                      </div>
                      <div class="col-12 col-lg-6">
                        <div class="wizard-step" id="wizardStep2">
                          <div class="fw-semibold mb-1">Step 2 - Resource</div>
                          <input id="crudResource" class="form-control form-control-sm" placeholder="Product">
                        </div>
                      </div>
                      <div class="col-12">
                        <div class="wizard-step" id="wizardStep3">
                          <div class="fw-semibold mb-1">Step 3 - Run</div>
                          <div class="btn-list">
                            <button class="btn btn-outline-primary btn-sm" id="btnMakeModule">Run Step 1</button>
                            <button class="btn btn-outline-primary btn-sm" id="btnMakeCrud">Run Step 2</button>
                            <button class="btn btn-primary btn-sm" id="btnRunWizard">Run Wizard</button>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="tab-pane" id="ops-mail">
                    <div class="text-secondary mb-2">Mail driver ve SMTP dogrulama</div>
                    <div class="input-group input-group-sm">
                      <input id="mailTo" class="form-control" placeholder="you@example.com">
                      <button class="btn btn-primary" id="btnMailTest">Send Test</button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-12 col-xl-4">
            <div class="card h-100">
              <div class="card-header py-2"><h3 class="card-title">Developer Lab</h3></div>
              <div class="list-group list-group-flush list-compact">
                <a class="list-group-item list-group-item-action" href="/kirpi/admin-demo" target="_blank" rel="noreferrer">Dashboard</a>
                <a class="list-group-item list-group-item-action" href="/kirpi/ui-kit" target="_blank" rel="noreferrer">UI Kit</a>
                <a class="list-group-item list-group-item-action" href="/kirpi/notify-test" target="_blank" rel="noreferrer">Notify Test</a>
                <a class="list-group-item list-group-item-action" href="/kirpi/api-notify-test" target="_blank" rel="noreferrer">API Notify Test</a>
                <a class="list-group-item list-group-item-action" href="/kirpi/pwa-test" target="_blank" rel="noreferrer">PWA Test</a>
                <a class="list-group-item list-group-item-action" href="/kirpi/modal-test" target="_blank" rel="noreferrer">Modal Test</a>
                <a class="list-group-item list-group-item-action" href="/kirpi/import-export-test" target="_blank" rel="noreferrer">Import/Export Test</a>
                <a class="list-group-item list-group-item-action" href="/kirpi/state-test" target="_blank" rel="noreferrer">State Test</a>
                <a class="list-group-item list-group-item-action" href="/kirpi/a11y-test" target="_blank" rel="noreferrer">A11y Test</a>
                <a class="list-group-item list-group-item-action" href="/kirpi-monitor" target="_blank" rel="noreferrer">Monitor</a>
                <a class="list-group-item list-group-item-action" href="/kirpi" target="_blank" rel="noreferrer">Runtime Dashboard</a>
              </div>
            </div>
          </div>

          <div class="col-12">
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
  const moduleInput = document.getElementById('moduleName');
  const resourceInput = document.getElementById('crudResource');
  const activityLog = document.getElementById('activityLog');
  const wizardStep1 = document.getElementById('wizardStep1');
  const wizardStep2 = document.getElementById('wizardStep2');
  const wizardStep3 = document.getElementById('wizardStep3');

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

  const setWizardStep = (step) => {
    [wizardStep1, wizardStep2, wizardStep3].forEach((el, idx) => {
      if (!el) return;
      el.classList.toggle('is-active', idx === (step - 1));
    });
  };

  const applyOverviewKpi = (payload) => {
    const data = payload?.data || {};
    if (kpiContext) kpiContext.textContent = String(data.context || '-');
    if (kpiRoutes) kpiRoutes.textContent = String(data.routes_total ?? '-');
    if (kpiModules) kpiModules.textContent = String(data.modules_total ?? '-');
    if (kpiFeatures) {
      const features = data.features || {};
      const enabled = Object.keys(features).filter((key) => features[key] === true).length;
      kpiFeatures.textContent = enabled + '/3 enabled';
    }
  };

  const callApi = async (url, label = '') => {
    const token = String(tokenInput.value || '').trim();
    const hasQuery = url.includes('?');
    const withToken = url + (hasQuery ? '&' : '?') + 'token=' + encodeURIComponent(token);
    const res = await fetch(withToken, {
      headers: {
        'Accept': 'application/json',
        'X-Manager-Token': token,
      },
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

  document.getElementById('btnMakeModule')?.addEventListener('click', async () => {
    const name = encodeURIComponent(String(moduleInput?.value || '').trim());
    setWizardStep(1);
    const result = await callApi('/manager/api/generate/module?name=' + name, 'Generate Module');
    if (result?.ok) setWizardStep(2);
  });

  document.getElementById('btnMakeCrud')?.addEventListener('click', async () => {
    const module = encodeURIComponent(String(moduleInput?.value || '').trim());
    const resource = encodeURIComponent(String(resourceInput?.value || '').trim());
    setWizardStep(2);
    const result = await callApi('/manager/api/generate/crud?module=' + module + '&resource=' + resource, 'Generate CRUD');
    if (result?.ok) setWizardStep(3);
  });

  document.getElementById('btnRunWizard')?.addEventListener('click', async () => {
    const module = encodeURIComponent(String(moduleInput?.value || '').trim());
    const resource = encodeURIComponent(String(resourceInput?.value || '').trim());

    setWizardStep(1);
    const first = await callApi('/manager/api/generate/module?name=' + module, 'Wizard Step 1');
    if (!first || first.ok !== true) return;

    setWizardStep(2);
    const second = await callApi('/manager/api/generate/crud?module=' + module + '&resource=' + resource, 'Wizard Step 2');
    if (!second || second.ok !== true) return;

    setWizardStep(3);
    write({ ok: true, message: 'Wizard tamamlandi', module: decodeURIComponent(module), resource: decodeURIComponent(resource) });
    appendLog('Wizard completed');
  });

  document.getElementById('btnMailTest')?.addEventListener('click', () => {
    const to = encodeURIComponent(String(document.getElementById('mailTo')?.value || '').trim());
    callApi('/manager/api/mail/test?to=' + to, 'Mail Test');
  });

  (async () => {
    const payload = await callApi('/manager/api/overview', 'Overview (autoload)');
    if (payload?.ok) applyOverviewKpi(payload);
  })();
})();
</script>
</body>
</html>
