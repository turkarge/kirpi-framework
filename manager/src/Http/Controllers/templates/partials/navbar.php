<?php
declare(strict_types=1);
/** @var string $token */
/** @var string $appEnv */
/** @var string $phpVersion */
/** @var string $currentPath */
$tokenQuery = $token !== '' ? ('?token=' . rawurlencode($token)) : '';
$isTopActive = static fn (array $paths): string => in_array($currentPath, $paths, true) ? 'active' : '';
?>
<header class="navbar navbar-expand-md d-print-none">
  <div class="container-xl">
    <h1 class="navbar-brand mb-0">Kirpi Manager</h1>
    <div class="navbar-nav me-auto">
      <a class="nav-link <?= $isTopActive(['/manager', '/manager/core']) ?>" href="/manager/core<?= $tokenQuery ?>">Core</a>
      <a class="nav-link <?= $isTopActive(['/manager/modules', '/manager/custom-modules']) ?>" href="/manager/modules<?= $tokenQuery ?>">Modules</a>
      <a class="nav-link <?= $isTopActive(['/manager/integrations', '/manager/mail']) ?>" href="/manager/integrations<?= $tokenQuery ?>">Integrations</a>
      <a class="nav-link <?= $isTopActive(['/manager/developer', '/manager/tests']) ?>" href="/manager/developer<?= $tokenQuery ?>">Developer</a>
      <a class="nav-link <?= $isTopActive(['/manager/system', '/manager/backup']) ?>" href="/manager/system<?= $tokenQuery ?>">System</a>
    </div>
    <div class="navbar-nav ms-auto">
      <a class="nav-link" href="/kirpi/admin-demo" target="_blank" rel="noreferrer">App</a>
      <span class="nav-link disabled">ENV: <?= htmlspecialchars($appEnv, ENT_QUOTES, 'UTF-8') ?></span>
      <span class="nav-link disabled">PHP: <?= htmlspecialchars($phpVersion, ENT_QUOTES, 'UTF-8') ?></span>
    </div>
  </div>
</header>
