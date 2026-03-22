<?php
declare(strict_types=1);
/** @var string $token */
/** @var string $currentPath */
$tokenQuery = $token !== '' ? ('?token=' . rawurlencode($token)) : '';
$active = static fn (string $path): string => $currentPath === $path ? 'active' : '';
?>
<div class="card">
  <div class="card-body p-2">
    <div class="list-group list-group-transparent">
      <div class="list-group-header">Core</div>
      <a class="list-group-item list-group-item-action <?= $active('/manager') ?>" href="/manager<?= $tokenQuery ?>">Dashboard</a>
      <a class="list-group-item list-group-item-action" href="/kirpi<?= $tokenQuery ?>" target="_blank" rel="noreferrer">Runtime</a>
      <a class="list-group-item list-group-item-action" href="/health<?= $tokenQuery ?>" target="_blank" rel="noreferrer">Health</a>
      <a class="list-group-item list-group-item-action" href="/kirpi-monitor<?= $tokenQuery ?>" target="_blank" rel="noreferrer">Monitor</a>

      <div class="list-group-header mt-2">Modules</div>
      <a class="list-group-item list-group-item-action <?= $active('/manager/modules') ?>" href="/manager/modules<?= $tokenQuery ?>">System Modules</a>
      <a class="list-group-item list-group-item-action <?= $active('/manager/custom-modules') ?>" href="/manager/custom-modules<?= $tokenQuery ?>">Custom Modules</a>

      <div class="list-group-header mt-2">Integrations</div>
      <a class="list-group-item list-group-item-action <?= $active('/manager/mail') ?>" href="/manager/mail<?= $tokenQuery ?>">Mail Settings/Test</a>

      <div class="list-group-header mt-2">Developer</div>
      <a class="list-group-item list-group-item-action <?= $active('/manager/tests') ?>" href="/manager/tests<?= $tokenQuery ?>">Test Screens</a>

      <div class="list-group-header mt-2">System</div>
      <a class="list-group-item list-group-item-action <?= $active('/manager/system') ?>" href="/manager/system<?= $tokenQuery ?>">Env & Feature Flags</a>
      <a class="list-group-item list-group-item-action <?= $active('/manager/backup') ?>" href="/manager/backup<?= $tokenQuery ?>">Backup Center</a>
    </div>
  </div>
</div>
