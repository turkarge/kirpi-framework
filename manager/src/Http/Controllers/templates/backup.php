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
  <title>Kirpi Manager - Backup Center</title>
  <link rel="stylesheet" href="/vendor/tabler/dist/css/tabler.min.css">
  <style>pre{max-height:520px;overflow:auto;margin:0}</style>
</head>
<body>
<div class="page">
  <?php require __DIR__ . '/partials/navbar.php'; ?>
  <div class="page-wrapper"><div class="page-body py-2"><div class="container-xl"><div class="row g-2">
    <div class="col-12 col-xl-3"><?php require __DIR__ . '/partials/sidebar.php'; ?></div>
    <div class="col-12 col-xl-9">
      <div class="row row-cards g-2">
        <div class="col-12 col-xl-4">
          <div class="card">
            <div class="card-header py-2"><h3 class="card-title">Backup Actions</h3></div>
            <div class="card-body">
              <input id="managerToken" class="form-control form-control-sm mb-2" type="text" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>" placeholder="KIRPI_MANAGER_TOKEN">
              <div class="d-grid gap-2">
                <button class="btn btn-primary btn-sm" id="btnCreateFull">Create Full Backup</button>
                <button class="btn btn-outline-primary btn-sm" id="btnCreateDb">Create DB Backup</button>
                <button class="btn btn-outline-primary btn-sm" id="btnList">Refresh Backup List</button>
              </div>
              <hr>
              <div class="small text-secondary">
                Full: DB + .env + storage/app snapshot<br>
                DB: only database dump<br>
                Checksum: SHA-256
              </div>
            </div>
          </div>
        </div>
        <div class="col-12 col-xl-8">
          <div class="card">
            <div class="card-header py-2"><h3 class="card-title">Backup Files</h3></div>
            <div class="table-responsive">
              <table class="table table-vcenter">
                <thead><tr><th>File</th><th>Size</th><th>Date</th><th>Actions</th></tr></thead>
                <tbody id="backupRows">
                  <tr><td colspan="4" class="text-secondary">No backups loaded.</td></tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        <div class="col-12">
          <div class="card"><div class="card-header py-2"><h3 class="card-title">Output</h3></div><div class="card-body p-0"><pre class="p-3" id="output">No action yet.</pre></div></div>
        </div>
      </div>
    </div>
  </div></div></div></div>
</div>
<script src="/vendor/tabler/dist/js/tabler.min.js"></script>
<script>
(() => {
  const output = document.getElementById('output');
  const tokenInput = document.getElementById('managerToken');
  const rows = document.getElementById('backupRows');

  const write = (payload) => { output.textContent = JSON.stringify(payload, null, 2); };
  const formatBytes = (n) => {
    const v = Number(n || 0);
    if (v < 1024) return v + ' B';
    if (v < 1024 * 1024) return (v / 1024).toFixed(1) + ' KB';
    return (v / (1024 * 1024)).toFixed(1) + ' MB';
  };

  const callApi = async (url) => {
    const token = String(tokenInput.value || '').trim();
    const withToken = url + (url.includes('?') ? '&' : '?') + 'token=' + encodeURIComponent(token);
    const res = await fetch(withToken, { headers: { 'Accept': 'application/json', 'X-Manager-Token': token } });
    let payload;
    try { payload = await res.json(); } catch (_e) { payload = { ok: false, error: 'Non-JSON response', status: res.status }; }
    write(payload);
    return payload;
  };

  const renderRows = (items) => {
    if (!Array.isArray(items) || items.length === 0) {
      rows.innerHTML = '<tr><td colspan="4" class="text-secondary">No backup files found.</td></tr>';
      return;
    }

    rows.innerHTML = '';
    for (const item of items) {
      const tr = document.createElement('tr');
      const file = String(item.file || '');
      const token = encodeURIComponent(String(tokenInput.value || '').trim());
      tr.innerHTML = `
        <td>${file}</td>
        <td>${formatBytes(item.size_bytes)}</td>
        <td>${item.created_at || '-'}</td>
        <td class="text-nowrap">
          <a class="btn btn-outline-primary btn-sm" href="/manager/api/backup/download?file=${encodeURIComponent(file)}&token=${token}" target="_blank" rel="noreferrer">Download</a>
          <button class="btn btn-outline-secondary btn-sm" data-action="verify" data-file="${file}">Verify</button>
          <button class="btn btn-outline-danger btn-sm" data-action="delete" data-file="${file}">Delete</button>
        </td>`;
      rows.appendChild(tr);
    }
  };

  const refreshList = async () => {
    const payload = await callApi('/manager/api/backup/list');
    if (payload?.ok) renderRows(payload.data || []);
  };

  document.getElementById('btnCreateFull')?.addEventListener('click', async () => {
    await callApi('/manager/api/backup/create?mode=full');
    await refreshList();
  });
  document.getElementById('btnCreateDb')?.addEventListener('click', async () => {
    await callApi('/manager/api/backup/create?mode=db');
    await refreshList();
  });
  document.getElementById('btnList')?.addEventListener('click', refreshList);

  rows.addEventListener('click', async (event) => {
    const target = event.target;
    if (!(target instanceof HTMLElement)) return;
    const action = target.getAttribute('data-action');
    const file = target.getAttribute('data-file');
    if (!action || !file) return;

    if (action === 'verify') {
      await callApi('/manager/api/backup/verify?file=' + encodeURIComponent(file));
      return;
    }
    if (action === 'delete') {
      if (!confirm('Delete backup file: ' + file + ' ?')) return;
      await callApi('/manager/api/backup/delete?file=' + encodeURIComponent(file));
      await refreshList();
    }
  });

  refreshList();
})();
</script>
</body>
</html>

