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
    <div class="page-header d-print-none">
      <div class="container-xl">
        <div class="row g-2 align-items-center">
          <div class="col">
            <div class="page-pretitle">KIRPI FRAMEWORK</div>
            <h2 class="page-title">Control Plane</h2>
            <div class="text-secondary">Dev lab, runtime ve generation operasyonlari tek panelde.</div>
          </div>
          <div class="col-auto">
            <a class="btn btn-primary" href="/kirpi/admin-demo" target="_blank" rel="noreferrer">Open Dashboard</a>
          </div>
        </div>
      </div>
    </div>

    <div class="page-body">
      <div class="container-xl">
        <div class="row row-cards">
          <div class="col-12 col-xl-4">
            <div class="card">
              <div class="card-header"><h3 class="card-title">Manager Token</h3></div>
              <div class="card-body">
                <input id="managerToken" class="form-control" type="text" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>" placeholder="KIRPI_MANAGER_TOKEN">
                <div class="form-hint mt-2">Manager API istekleri bu token ile dogrulanir.</div>
              </div>
            </div>
          </div>

          <div class="col-12 col-xl-8">
            <div class="card">
              <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs" data-bs-toggle="tabs">
                  <li class="nav-item"><a href="#tab-overview" class="nav-link active" data-bs-toggle="tab">Overview</a></li>
                  <li class="nav-item"><a href="#tab-runtime" class="nav-link" data-bs-toggle="tab">Runtime</a></li>
                  <li class="nav-item"><a href="#tab-lab" class="nav-link" data-bs-toggle="tab">Dev Lab</a></li>
                </ul>
              </div>
              <div class="card-body tab-content">
                <div class="tab-pane active" id="tab-overview">
                  <div class="btn-list">
                    <button class="btn btn-primary" id="btnOverview">Load Overview</button>
                    <button class="btn btn-outline-primary" id="btnModules">Load Modules</button>
                    <button class="btn btn-outline-primary" id="btnEnv">Load Env (masked)</button>
                  </div>
                </div>
                <div class="tab-pane" id="tab-runtime">
                  <div class="btn-list">
                    <button class="btn btn-primary" id="btnRuntimeReady">Runtime Ready</button>
                    <button class="btn btn-outline-primary" id="btnRuntimeSelfCheck">Self Check</button>
                    <button class="btn btn-outline-primary" id="btnRuntimeHistory">Self Check History</button>
                  </div>
                </div>
                <div class="tab-pane" id="tab-lab">
                  <div class="d-flex flex-wrap gap-2">
                    <a class="btn btn-outline-secondary" href="/kirpi/admin-demo" target="_blank" rel="noreferrer">Dashboard</a>
                    <a class="btn btn-outline-secondary" href="/kirpi/ui-kit" target="_blank" rel="noreferrer">UI Kit</a>
                    <a class="btn btn-outline-secondary" href="/kirpi/notify-test" target="_blank" rel="noreferrer">Notify Test</a>
                    <a class="btn btn-outline-secondary" href="/kirpi/api-notify-test" target="_blank" rel="noreferrer">API Notify</a>
                    <a class="btn btn-outline-secondary" href="/kirpi/pwa-test" target="_blank" rel="noreferrer">PWA Test</a>
                    <a class="btn btn-outline-secondary" href="/kirpi/modal-test" target="_blank" rel="noreferrer">Modal Test</a>
                    <a class="btn btn-outline-secondary" href="/kirpi/import-export-test" target="_blank" rel="noreferrer">Import/Export</a>
                    <a class="btn btn-outline-secondary" href="/kirpi/state-test" target="_blank" rel="noreferrer">State Test</a>
                    <a class="btn btn-outline-secondary" href="/kirpi/a11y-test" target="_blank" rel="noreferrer">A11y Test</a>
                    <a class="btn btn-outline-secondary" href="/kirpi-monitor" target="_blank" rel="noreferrer">Monitor</a>
                    <a class="btn btn-outline-secondary" href="/kirpi" target="_blank" rel="noreferrer">Runtime</a>
                    <a class="btn btn-outline-secondary" href="/health" target="_blank" rel="noreferrer">Health</a>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-12 col-xl-7">
            <div class="card">
              <div class="card-header"><h3 class="card-title">Module Wizard</h3></div>
              <div class="card-body">
                <div class="wizard-step is-active" id="wizardStep1">
                  <div class="h4 mb-2">Step 1 - Module</div>
                  <input id="moduleName" class="form-control" placeholder="Catalog">
                  <div class="form-hint">Yeni modul klasoru ve temel dosyalari uretilir.</div>
                </div>
                <div class="wizard-step mt-3" id="wizardStep2">
                  <div class="h4 mb-2">Step 2 - Resource</div>
                  <input id="crudResource" class="form-control" placeholder="Product">
                  <div class="form-hint">CRUD controller/model/request/routes olusturulur.</div>
                </div>
                <div class="wizard-step mt-3" id="wizardStep3">
                  <div class="h4 mb-2">Step 3 - Run</div>
                  <div class="btn-list">
                    <button class="btn btn-outline-primary" id="btnMakeModule">Run Step 1</button>
                    <button class="btn btn-outline-primary" id="btnMakeCrud">Run Step 2</button>
                    <button class="btn btn-primary" id="btnRunWizard">Run Wizard</button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-12 col-xl-5">
            <div class="card">
              <div class="card-header"><h3 class="card-title">Mail Test</h3></div>
              <div class="card-body">
                <div class="input-group">
                  <input id="mailTo" class="form-control" placeholder="you@example.com">
                  <button class="btn btn-primary" id="btnMailTest">Send</button>
                </div>
                <div class="form-hint mt-2">Mail driver ve SMTP ayarlarini hizli dogrulamak icin.</div>
              </div>
            </div>
          </div>

          <div class="col-12">
            <div class="card">
              <div class="card-header"><h3 class="card-title">Output</h3></div>
              <div class="card-body p-0"><pre class="p-3" id="output">No action yet.</pre></div>
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
  const wizardStep1 = document.getElementById('wizardStep1');
  const wizardStep2 = document.getElementById('wizardStep2');
  const wizardStep3 = document.getElementById('wizardStep3');

  const write = (payload) => { output.textContent = JSON.stringify(payload, null, 2); };
  const setWizardStep = (step) => {
    [wizardStep1, wizardStep2, wizardStep3].forEach((el, idx) => {
      if (!el) return;
      el.classList.toggle('is-active', idx === (step - 1));
    });
  };

  const callApi = async (url) => {
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
    return payload;
  };

  document.getElementById('btnOverview')?.addEventListener('click', () => callApi('/manager/api/overview'));
  document.getElementById('btnModules')?.addEventListener('click', () => callApi('/manager/api/modules'));
  document.getElementById('btnEnv')?.addEventListener('click', () => callApi('/manager/api/env'));

  document.getElementById('btnRuntimeReady')?.addEventListener('click', () => callApi('/manager/api/runtime/ready'));
  document.getElementById('btnRuntimeSelfCheck')?.addEventListener('click', () => callApi('/manager/api/runtime/self-check'));
  document.getElementById('btnRuntimeHistory')?.addEventListener('click', () => callApi('/manager/api/runtime/history'));

  document.getElementById('btnMakeModule')?.addEventListener('click', async () => {
    const name = encodeURIComponent(String(moduleInput?.value || '').trim());
    setWizardStep(1);
    await callApi('/manager/api/generate/module?name=' + name);
    setWizardStep(2);
  });

  document.getElementById('btnMakeCrud')?.addEventListener('click', async () => {
    const module = encodeURIComponent(String(moduleInput?.value || '').trim());
    const resource = encodeURIComponent(String(resourceInput?.value || '').trim());
    setWizardStep(2);
    await callApi('/manager/api/generate/crud?module=' + module + '&resource=' + resource);
    setWizardStep(3);
  });

  document.getElementById('btnRunWizard')?.addEventListener('click', async () => {
    const module = encodeURIComponent(String(moduleInput?.value || '').trim());
    const resource = encodeURIComponent(String(resourceInput?.value || '').trim());
    setWizardStep(1);
    const first = await callApi('/manager/api/generate/module?name=' + module);
    if (!first || first.ok !== true) return;
    setWizardStep(2);
    const second = await callApi('/manager/api/generate/crud?module=' + module + '&resource=' + resource);
    if (!second || second.ok !== true) return;
    setWizardStep(3);
    write({ ok: true, message: 'Wizard tamamlandi', module: decodeURIComponent(module), resource: decodeURIComponent(resource) });
  });

  document.getElementById('btnMailTest')?.addEventListener('click', () => {
    const to = encodeURIComponent(String(document.getElementById('mailTo')?.value || '').trim());
    callApi('/manager/api/mail/test?to=' + to);
  });
})();
</script>
</body>
</html>
