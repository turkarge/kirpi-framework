<?php
declare(strict_types=1);
/** @var string $token */
/** @var string $appEnv */
/** @var string $appUrl */
/** @var string $phpVersion */
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kirpi Manager</title>
  <link rel="stylesheet" href="/vendor/tabler/dist/css/tabler.min.css">
</head>
<body>
<div class="page">
  <div class="page-wrapper">
    <div class="page-header d-print-none">
      <div class="container-xl">
        <div class="row g-2 align-items-center">
          <div class="col">
            <h2 class="page-title">Kirpi Manager</h2>
            <div class="text-secondary">Sadece sistem sagligi ve API durumu.</div>
          </div>
          <div class="col-auto">
            <a class="btn btn-outline-primary" href="/monitor">Monitor</a>
          </div>
        </div>
      </div>
    </div>
    <div class="page-body">
      <div class="container-xl">
        <div class="row g-3">
          <div class="col-12 col-lg-4">
            <div class="card">
              <div class="card-header"><h3 class="card-title">Baglanti</h3></div>
              <div class="card-body">
                <div class="mb-2"><strong>ENV:</strong> <?= htmlspecialchars($appEnv, ENT_QUOTES, 'UTF-8') ?></div>
                <div class="mb-2"><strong>APP_URL:</strong> <?= htmlspecialchars($appUrl, ENT_QUOTES, 'UTF-8') ?></div>
                <div class="mb-2"><strong>PHP:</strong> <?= htmlspecialchars($phpVersion, ENT_QUOTES, 'UTF-8') ?></div>
                <label class="form-label mt-3">Manager Token</label>
                <input id="managerToken" class="form-control" type="text" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>" placeholder="KIRPI_MANAGER_TOKEN">
              </div>
            </div>
          </div>
          <div class="col-12 col-lg-8">
            <div class="card">
              <div class="card-header"><h3 class="card-title">Sistem Durumu</h3></div>
              <div class="card-body">
                <div class="d-flex flex-wrap gap-2 mb-3">
                  <button class="btn btn-primary" id="btnOverview">Overview</button>
                  <button class="btn btn-outline-primary" id="btnHealth">Health</button>
                  <button class="btn btn-outline-primary" id="btnReady">Ready</button>
                </div>
                <pre id="output" class="bg-body-tertiary p-3 rounded m-0" style="max-height:420px;overflow:auto">Henüz sorgu yapilmadi.</pre>
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

  const callApi = async (path) => {
    const token = String(tokenInput.value || '').trim();
    const url = path + (path.includes('?') ? '&' : '?') + 'token=' + encodeURIComponent(token);
    const res = await fetch(url, { headers: { 'Accept': 'application/json', 'X-Manager-Token': token } });
    const json = await res.json();
    output.textContent = JSON.stringify(json, null, 2);
  };

  document.getElementById('btnOverview')?.addEventListener('click', () => callApi('/manager/api/overview'));
  document.getElementById('btnHealth')?.addEventListener('click', () => callApi('/manager/api/health'));
  document.getElementById('btnReady')?.addEventListener('click', () => callApi('/manager/api/ready'));

  callApi('/manager/api/overview');
})();
</script>
</body>
</html>
