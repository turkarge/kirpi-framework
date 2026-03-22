<?php
declare(strict_types=1);
/** @var string $token */
/** @var string $appEnv */
/** @var string $appUrl */
/** @var string $phpVersion */
/** @var string $currentPath */
$tokenQuery = $token !== '' ? ('?token=' . rawurlencode($token)) : '';
?>
<!DOCTYPE html><html lang="en" data-bs-theme="light"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Kirpi Manager - Integrations</title><link rel="stylesheet" href="/vendor/tabler/dist/css/tabler.min.css"></head><body>
<div class="page"><?php require __DIR__ . '/partials/navbar.php'; ?><div class="page-wrapper"><div class="page-body py-2"><div class="container-xl"><div class="row g-2">
<div class="col-12 col-xl-3"><?php require __DIR__ . '/partials/sidebar.php'; ?></div>
<div class="col-12 col-xl-9"><div class="row row-cards g-2">
<div class="col-12 col-md-6"><a class="card card-link" href="/manager/mail<?= $tokenQuery ?>"><div class="card-body"><h3 class="card-title">Mail Settings/Test</h3><div class="text-secondary">SMTP ve driver dogrulamasi.</div></div></a></div>
<div class="col-12 col-md-6"><div class="card"><div class="card-body"><h3 class="card-title">Storage</h3><div class="text-secondary">Sonraki iterasyonda baglanacak.</div></div></div></div>
<div class="col-12 col-md-6"><div class="card"><div class="card-body"><h3 class="card-title">Queue</h3><div class="text-secondary">Sonraki iterasyonda baglanacak.</div></div></div></div>
</div></div>
</div></div></div></div>
</div><script src="/vendor/tabler/dist/js/tabler.min.js"></script></body></html>
