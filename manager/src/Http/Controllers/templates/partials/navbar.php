<?php
declare(strict_types=1);
/** @var string $token */
/** @var string $appEnv */
/** @var string $phpVersion */
/** @var string $currentPath */
$tokenQuery = $token !== '' ? ('?token=' . rawurlencode($token)) : '';
$active = static fn (string $path): string => $currentPath === $path ? 'active' : '';
?>
<header class="navbar navbar-expand-md d-print-none">
  <div class="container-xl">
    <h1 class="navbar-brand mb-0">Kirpi Manager</h1>
    <div class="navbar-nav me-auto">
      <a class="nav-link <?= $active('/manager') ?>" href="/manager<?= $tokenQuery ?>">Dashboard</a>
      <a class="nav-link <?= $active('/manager/modules') ?>" href="/manager/modules<?= $tokenQuery ?>">System Modules</a>
      <a class="nav-link <?= $active('/manager/custom-modules') ?>" href="/manager/custom-modules<?= $tokenQuery ?>">Custom Modules</a>
      <a class="nav-link <?= $active('/manager/mail') ?>" href="/manager/mail<?= $tokenQuery ?>">Mail</a>
      <a class="nav-link <?= $active('/manager/tests') ?>" href="/manager/tests<?= $tokenQuery ?>">Test Screens</a>
      <div class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Developer Lab</a>
        <div class="dropdown-menu dropdown-menu-arrow">
          <a class="dropdown-item" href="/kirpi/admin-demo" target="_blank" rel="noreferrer">Dashboard</a>
          <a class="dropdown-item" href="/kirpi/ui-kit" target="_blank" rel="noreferrer">UI Kit</a>
          <a class="dropdown-item" href="/kirpi/notify-test" target="_blank" rel="noreferrer">Notify Test</a>
          <a class="dropdown-item" href="/kirpi/api-notify-test" target="_blank" rel="noreferrer">API Notify Test</a>
          <a class="dropdown-item" href="/kirpi/pwa-test" target="_blank" rel="noreferrer">PWA Test</a>
          <a class="dropdown-item" href="/kirpi/modal-test" target="_blank" rel="noreferrer">Modal Test</a>
          <a class="dropdown-item" href="/kirpi/import-export-test" target="_blank" rel="noreferrer">Import/Export Test</a>
          <a class="dropdown-item" href="/kirpi/state-test" target="_blank" rel="noreferrer">State Test</a>
          <a class="dropdown-item" href="/kirpi/a11y-test" target="_blank" rel="noreferrer">A11y Test</a>
          <a class="dropdown-item" href="/kirpi-monitor" target="_blank" rel="noreferrer">Monitor</a>
          <a class="dropdown-item" href="/kirpi" target="_blank" rel="noreferrer">Runtime</a>
        </div>
      </div>
    </div>
    <div class="navbar-nav ms-auto">
      <span class="nav-link disabled">ENV: <?= htmlspecialchars($appEnv, ENT_QUOTES, 'UTF-8') ?></span>
      <span class="nav-link disabled">PHP: <?= htmlspecialchars($phpVersion, ENT_QUOTES, 'UTF-8') ?></span>
    </div>
  </div>
</header>
