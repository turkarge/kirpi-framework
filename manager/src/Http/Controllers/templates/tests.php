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
  <title>Kirpi Manager - Test Screens</title>
  <link rel="stylesheet" href="/vendor/tabler/dist/css/tabler.min.css">
</head>
<body>
<div class="page">
  <?php require __DIR__ . '/partials/navbar.php'; ?>
  <div class="page-wrapper">
    <div class="page-header py-2"><div class="container-xl"><h2 class="page-title">Test Screens</h2></div></div>
    <div class="page-body py-2">
      <div class="container-xl">
        <div class="row row-cards g-2">
          <div class="col-12 col-md-6 col-xl-4"><a class="card card-link" href="/kirpi/admin-demo" target="_blank" rel="noreferrer"><div class="card-body">Dashboard</div></a></div>
          <div class="col-12 col-md-6 col-xl-4"><a class="card card-link" href="/kirpi/ui-kit" target="_blank" rel="noreferrer"><div class="card-body">UI Kit</div></a></div>
          <div class="col-12 col-md-6 col-xl-4"><a class="card card-link" href="/kirpi/notify-test" target="_blank" rel="noreferrer"><div class="card-body">Notify Test</div></a></div>
          <div class="col-12 col-md-6 col-xl-4"><a class="card card-link" href="/kirpi/api-notify-test" target="_blank" rel="noreferrer"><div class="card-body">API Notify Test</div></a></div>
          <div class="col-12 col-md-6 col-xl-4"><a class="card card-link" href="/kirpi/pwa-test" target="_blank" rel="noreferrer"><div class="card-body">PWA Test</div></a></div>
          <div class="col-12 col-md-6 col-xl-4"><a class="card card-link" href="/kirpi/modal-test" target="_blank" rel="noreferrer"><div class="card-body">Modal Test</div></a></div>
          <div class="col-12 col-md-6 col-xl-4"><a class="card card-link" href="/kirpi/import-export-test" target="_blank" rel="noreferrer"><div class="card-body">Import/Export Test</div></a></div>
          <div class="col-12 col-md-6 col-xl-4"><a class="card card-link" href="/kirpi/state-test" target="_blank" rel="noreferrer"><div class="card-body">State Test</div></a></div>
          <div class="col-12 col-md-6 col-xl-4"><a class="card card-link" href="/kirpi/a11y-test" target="_blank" rel="noreferrer"><div class="card-body">A11y Test</div></a></div>
          <div class="col-12 col-md-6 col-xl-4"><a class="card card-link" href="/kirpi-monitor" target="_blank" rel="noreferrer"><div class="card-body">Monitor</div></a></div>
          <div class="col-12 col-md-6 col-xl-4"><a class="card card-link" href="/kirpi" target="_blank" rel="noreferrer"><div class="card-body">Runtime</div></a></div>
        </div>
      </div>
    </div>
  </div>
</div>
<script src="/vendor/tabler/dist/js/tabler.min.js"></script>
</body>
</html>
