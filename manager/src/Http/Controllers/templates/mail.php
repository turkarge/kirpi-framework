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
  <title>Kirpi Manager - Mail</title>
  <link rel="stylesheet" href="/vendor/tabler/dist/css/tabler.min.css">
  <style>pre{max-height:520px;overflow:auto;margin:0;}</style>
</head>
<body>
<div class="page">
  <?php require __DIR__ . '/partials/navbar.php'; ?>
  <div class="page-wrapper">
    <div class="page-header py-2"><div class="container-xl"><h2 class="page-title">Mail Settings & Test</h2></div></div>
    <div class="page-body py-2">
      <div class="container-xl">
        <div class="row row-cards g-2">
          <div class="col-12 col-xl-4">
            <div class="card"><div class="card-header py-2"><h3 class="card-title">Mail Test</h3></div><div class="card-body">
              <input id="managerToken" class="form-control form-control-sm mb-2" type="text" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>" placeholder="KIRPI_MANAGER_TOKEN">
              <input id="mailTo" class="form-control form-control-sm mb-2" placeholder="you@example.com">
              <button class="btn btn-primary btn-sm w-100" id="btnMailTest">Send Test Mail</button>
            </div></div>
          </div>
          <div class="col-12 col-xl-8">
            <div class="card"><div class="card-header py-2"><h3 class="card-title">Output</h3></div><div class="card-body p-0"><pre class="p-3" id="output">No action yet.</pre></div></div>
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
  const mailTo = document.getElementById('mailTo');
  const write = (payload) => { output.textContent = JSON.stringify(payload, null, 2); };
  const callApi = async (url) => {
    const token = String(tokenInput.value || '').trim();
    const withToken = url + (url.includes('?') ? '&' : '?') + 'token=' + encodeURIComponent(token);
    const res = await fetch(withToken, { headers: { 'Accept': 'application/json', 'X-Manager-Token': token } });
    write(await res.json());
  };
  document.getElementById('btnMailTest')?.addEventListener('click', () => {
    const to = encodeURIComponent(String(mailTo.value || '').trim());
    callApi('/manager/api/mail/test?to=' + to);
  });
})();
</script>
</body>
</html>
