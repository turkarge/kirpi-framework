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
  <title>Kirpi Manager - System</title>
  <link rel="stylesheet" href="/vendor/tabler/dist/css/tabler.min.css">
  <style>pre{max-height:520px;overflow:auto;margin:0;}</style>
</head>
<body>
<div class="page">
  <?php require __DIR__ . '/partials/navbar.php'; ?>
  <div class="page-wrapper"><div class="page-body py-2"><div class="container-xl"><div class="row g-2">
    <div class="col-12 col-xl-3"><?php require __DIR__ . '/partials/sidebar.php'; ?></div>
    <div class="col-12 col-xl-9">
      <div class="row row-cards g-2">
        <div class="col-12 col-xl-4">
          <div class="card"><div class="card-header py-2"><h3 class="card-title">System Actions</h3></div><div class="card-body">
            <input id="managerToken" class="form-control form-control-sm mb-2" type="text" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>" placeholder="KIRPI_MANAGER_TOKEN">
            <div class="d-grid gap-2">
              <button class="btn btn-primary btn-sm" id="btnOverview">Feature Flags</button>
              <button class="btn btn-outline-primary btn-sm" id="btnEnv">Env Inspector</button>
              <a class="btn btn-outline-secondary btn-sm" href="/manager/backup<?= $token !== '' ? '?token=' . rawurlencode($token) : '' ?>">Backup Center</a>
            </div>
            <hr>
            <div class="small text-secondary">Kirpi Framework<br>PHP <?= htmlspecialchars($phpVersion, ENT_QUOTES, 'UTF-8') ?><br>ENV <?= htmlspecialchars($appEnv, ENT_QUOTES, 'UTF-8') ?></div>
          </div></div>
        </div>
        <div class="col-12 col-xl-8"><div class="card"><div class="card-header py-2"><h3 class="card-title">Output</h3></div><div class="card-body p-0"><pre class="p-3" id="output">No action yet.</pre></div></div></div>
      </div>
    </div>
  </div></div></div></div>
</div>
<script src="/vendor/tabler/dist/js/tabler.min.js"></script>
<script>
(() => {
  const output = document.getElementById('output');
  const tokenInput = document.getElementById('managerToken');
  const write = (payload) => { output.textContent = JSON.stringify(payload, null, 2); };
  const callApi = async (url) => {
    const token = String(tokenInput.value || '').trim();
    const withToken = url + (url.includes('?') ? '&' : '?') + 'token=' + encodeURIComponent(token);
    const res = await fetch(withToken, { headers: { 'Accept': 'application/json', 'X-Manager-Token': token } });
    write(await res.json());
  };
  document.getElementById('btnOverview')?.addEventListener('click', () => callApi('/manager/api/overview'));
  document.getElementById('btnEnv')?.addEventListener('click', () => callApi('/manager/api/env'));
})();
</script>
</body>
</html>
