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
  <title>Kirpi Manager - Custom Modules</title>
  <link rel="stylesheet" href="/vendor/tabler/dist/css/tabler.min.css">
  <style>pre{max-height:520px;overflow:auto;margin:0;} .wizard-step{border-left:3px solid var(--tblr-border-color);padding-left:.75rem}.wizard-step.is-active{border-left-color:var(--tblr-primary)}</style>
</head>
<body>
<div class="page">
  <?php require __DIR__ . '/partials/navbar.php'; ?>
  <div class="page-wrapper">
    <div class="page-header py-2"><div class="container-xl"><h2 class="page-title">Custom Module Wizard</h2></div></div>
    <div class="page-body py-2">
      <div class="container-xl">
        <div class="row row-cards g-2">
          <div class="col-12 col-xl-4">
            <div class="card"><div class="card-header py-2"><h3 class="card-title">Wizard</h3></div><div class="card-body">
              <input id="managerToken" class="form-control form-control-sm mb-2" type="text" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>" placeholder="KIRPI_MANAGER_TOKEN">
              <div class="wizard-step is-active mb-2" id="wizardStep1"><div class="fw-semibold mb-1">Step 1</div><input id="moduleName" class="form-control form-control-sm" placeholder="Catalog"></div>
              <div class="wizard-step mb-2" id="wizardStep2"><div class="fw-semibold mb-1">Step 2</div><input id="crudResource" class="form-control form-control-sm" placeholder="Product"></div>
              <div class="wizard-step" id="wizardStep3"><div class="fw-semibold mb-1">Step 3</div><div class="d-grid gap-2"><button class="btn btn-outline-primary btn-sm" id="btnMakeModule">Run Step 1</button><button class="btn btn-outline-primary btn-sm" id="btnMakeCrud">Run Step 2</button><button class="btn btn-primary btn-sm" id="btnRunWizard">Run Wizard</button></div></div>
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
  const moduleInput = document.getElementById('moduleName');
  const resourceInput = document.getElementById('crudResource');
  const wizardStep1 = document.getElementById('wizardStep1');
  const wizardStep2 = document.getElementById('wizardStep2');
  const wizardStep3 = document.getElementById('wizardStep3');

  const write = (payload) => { output.textContent = JSON.stringify(payload, null, 2); };
  const setWizardStep = (step) => {
    [wizardStep1, wizardStep2, wizardStep3].forEach((el, idx) => el.classList.toggle('is-active', idx === (step - 1)));
  };
  const callApi = async (url) => {
    const token = String(tokenInput.value || '').trim();
    const withToken = url + (url.includes('?') ? '&' : '?') + 'token=' + encodeURIComponent(token);
    const res = await fetch(withToken, { headers: { 'Accept': 'application/json', 'X-Manager-Token': token } });
    const payload = await res.json();
    write(payload);
    return payload;
  };

  document.getElementById('btnMakeModule')?.addEventListener('click', async () => {
    const module = encodeURIComponent(String(moduleInput.value || '').trim());
    setWizardStep(1);
    const result = await callApi('/manager/api/generate/module?name=' + module);
    if (result?.ok) setWizardStep(2);
  });
  document.getElementById('btnMakeCrud')?.addEventListener('click', async () => {
    const module = encodeURIComponent(String(moduleInput.value || '').trim());
    const resource = encodeURIComponent(String(resourceInput.value || '').trim());
    setWizardStep(2);
    const result = await callApi('/manager/api/generate/crud?module=' + module + '&resource=' + resource);
    if (result?.ok) setWizardStep(3);
  });
  document.getElementById('btnRunWizard')?.addEventListener('click', async () => {
    const module = encodeURIComponent(String(moduleInput.value || '').trim());
    const resource = encodeURIComponent(String(resourceInput.value || '').trim());
    setWizardStep(1);
    const first = await callApi('/manager/api/generate/module?name=' + module);
    if (!first?.ok) return;
    setWizardStep(2);
    const second = await callApi('/manager/api/generate/crud?module=' + module + '&resource=' + resource);
    if (!second?.ok) return;
    setWizardStep(3);
    write({ ok: true, message: 'Wizard tamamlandi', module: decodeURIComponent(module), resource: decodeURIComponent(resource) });
  });
})();
</script>
</body>
</html>
