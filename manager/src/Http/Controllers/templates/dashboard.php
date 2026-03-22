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
    .manager-wrap { max-width: 1200px; margin: 0 auto; padding: 1rem; }
    pre { max-height: 340px; overflow: auto; }
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
          <h2 class="page-title">Control Plane</h2>
          <div class="text-secondary">Framework operasyon paneli. Command, env inspector, module aksiyonlari.</div>
          <div class="mt-2 text-secondary">Target: <?= htmlspecialchars($appUrl, ENT_QUOTES, 'UTF-8') ?></div>
        </div>
      </div>

      <div class="page-body">
        <div class="manager-wrap">
          <div class="row g-3">
            <div class="col-12 col-lg-4">
              <div class="card">
                <div class="card-header"><h3 class="card-title">Manager Token</h3></div>
                <div class="card-body">
                  <input id="managerToken" class="form-control" type="text" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>" placeholder="KIRPI_MANAGER_TOKEN">
                  <div class="form-hint mt-2">Tum istekler bu token ile gonderilir.</div>
                </div>
              </div>
            </div>

            <div class="col-12 col-lg-8">
              <div class="card">
                <div class="card-header"><h3 class="card-title">Overview</h3></div>
                <div class="card-body">
                  <button class="btn btn-primary" id="btnOverview">Load Overview</button>
                  <button class="btn btn-1" id="btnModules">Load Modules</button>
                  <button class="btn btn-1" id="btnEnv">Load Env (masked)</button>
                </div>
              </div>
            </div>

            <div class="col-12 col-lg-6">
              <div class="card">
                <div class="card-header"><h3 class="card-title">Generate Module</h3></div>
                <div class="card-body">
                  <div class="input-group">
                    <input id="moduleName" class="form-control" placeholder="Catalog">
                    <button class="btn btn-primary" id="btnMakeModule">Run make:module</button>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-12 col-lg-6">
              <div class="card">
                <div class="card-header"><h3 class="card-title">Generate CRUD</h3></div>
                <div class="card-body">
                  <div class="row g-2">
                    <div class="col-6"><input id="crudModule" class="form-control" placeholder="Catalog"></div>
                    <div class="col-6"><input id="crudResource" class="form-control" placeholder="Product"></div>
                  </div>
                  <button class="btn btn-primary mt-2" id="btnMakeCrud">Run make:crud</button>
                </div>
              </div>
            </div>

            <div class="col-12 col-lg-6">
              <div class="card">
                <div class="card-header"><h3 class="card-title">Mail Test</h3></div>
                <div class="card-body">
                  <div class="input-group">
                    <input id="mailTo" class="form-control" placeholder="you@example.com">
                    <button class="btn btn-primary" id="btnMailTest">Send Test Mail</button>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-12">
              <div class="card">
                <div class="card-header"><h3 class="card-title">Output</h3></div>
                <div class="card-body">
                  <pre id="output">No action yet.</pre>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    (() => {
      const output = document.getElementById('output');
      const tokenInput = document.getElementById('managerToken');
      const write = (payload) => { output.textContent = JSON.stringify(payload, null, 2); };

      const callApi = async (url) => {
        const token = String(tokenInput.value || '').trim();
        const hasQuery = url.includes('?');
        const withToken = url + (hasQuery ? '&' : '?') + 'token=' + encodeURIComponent(token);
        const res = await fetch(withToken, { headers: { 'Accept': 'application/json', 'X-Manager-Token': token } });
        const payload = await res.json();
        write(payload);
      };

      document.getElementById('btnOverview')?.addEventListener('click', () => callApi('/manager/api/overview'));
      document.getElementById('btnModules')?.addEventListener('click', () => callApi('/manager/api/modules'));
      document.getElementById('btnEnv')?.addEventListener('click', () => callApi('/manager/api/env'));
      document.getElementById('btnMakeModule')?.addEventListener('click', () => {
        const name = encodeURIComponent(String(document.getElementById('moduleName')?.value || '').trim());
        callApi('/manager/api/generate/module?name=' + name);
      });
      document.getElementById('btnMakeCrud')?.addEventListener('click', () => {
        const module = encodeURIComponent(String(document.getElementById('crudModule')?.value || '').trim());
        const resource = encodeURIComponent(String(document.getElementById('crudResource')?.value || '').trim());
        callApi('/manager/api/generate/crud?module=' + module + '&resource=' + resource);
      });
      document.getElementById('btnMailTest')?.addEventListener('click', () => {
        const to = encodeURIComponent(String(document.getElementById('mailTo')?.value || '').trim());
        callApi('/manager/api/mail/test?to=' + to);
      });
    })();
  </script>
</body>
</html>

