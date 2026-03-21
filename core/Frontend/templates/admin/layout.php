<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?= htmlspecialchars((string) $title, ENT_QUOTES, 'UTF-8') ?></title>
    <link href="/vendor/tabler/dist/css/tabler.css" rel="stylesheet">
    <link href="/vendor/tabler/dist/css/tabler-flags.css" rel="stylesheet">
    <link href="/vendor/tabler/dist/css/tabler-socials.css" rel="stylesheet">
    <link href="/vendor/tabler/dist/css/tabler-payments.css" rel="stylesheet">
    <link href="/vendor/tabler/dist/css/tabler-vendors.css" rel="stylesheet">
    <link href="/vendor/tabler/dist/css/tabler-marketing.css" rel="stylesheet">
    <link href="/vendor/tabler/dist/css/tabler-themes.css" rel="stylesheet">
    <link href="/vendor/tabler/preview/css/demo.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/admin.css">
    <style>
      @import url("https://rsms.me/inter/inter.css");
    </style>
</head>
<body class="layout-fluid">
    <script src="/vendor/tabler/dist/js/tabler-theme.min.js"></script>
    <div class="page">
        <header class="navbar navbar-expand-md d-print-none">
            <div class="container-xl">
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-menu" aria-controls="navbar-menu" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="navbar-brand navbar-brand-autodark pe-0 pe-md-3">
                    <a href="/kirpi/admin-demo" class="text-reset text-decoration-none fw-bold">Kirpi Runtime</a>
                </div>
                <div class="navbar-nav flex-row order-md-last">
                    <a class="nav-link" href="/kirpi/ui-kit"><span class="nav-link-title">UI Kit</span></a>
                    <a class="nav-link" href="/kirpi/notify-test"><span class="nav-link-title">Notify</span></a>
                    <a class="nav-link" href="/kirpi/api-notify-test"><span class="nav-link-title">API Notify</span></a>
                </div>
            </div>
        </header>

        <header class="navbar-expand-md">
            <div class="collapse navbar-collapse" id="navbar-menu">
                <div class="navbar">
                    <div class="container-xl">
                        <ul class="navbar-nav">
                            <li class="nav-item active"><a class="nav-link" href="/kirpi/admin-demo"><span class="nav-link-title">Dashboard</span></a></li>
                            <li class="nav-item"><a class="nav-link" href="/kirpi/ui-kit"><span class="nav-link-title">Components</span></a></li>
                            <li class="nav-item"><a class="nav-link" href="/kirpi/notify-test"><span class="nav-link-title">Flash Test</span></a></li>
                            <li class="nav-item"><a class="nav-link" href="/kirpi/api-notify-test"><span class="nav-link-title">API Test</span></a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </header>

        <div class="page-wrapper">
            <div class="page-header d-print-none">
                <div class="container-xl">
                    <div class="row g-2 align-items-center">
                        <div class="col">
                            <div class="page-pretitle">Overview</div>
                            <h2 class="page-title"><?= htmlspecialchars((string) ($heroTitle ?? 'Kirpi Admin UI Kit'), ENT_QUOTES, 'UTF-8') ?></h2>
                            <div class="text-secondary"><?= htmlspecialchars((string) ($heroSubtitle ?? 'Tekrar kullanilabilir admin bilesenleri icin temel gorunur alan.'), ENT_QUOTES, 'UTF-8') ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="page-body">
                <div class="container-xl">
                    <?= $content ?>
                </div>
            </div>
        </div>
    </div>

    <script src="/vendor/tabler/dist/js/tabler.min.js" defer></script>
    <script src="/vendor/tabler/preview/js/demo.min.js" defer></script>
    <?php require __DIR__ . '/partials/notify.php'; ?>
</body>
</html>
