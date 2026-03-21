<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars((string) $title, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/css/tabler.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <link rel="stylesheet" href="/assets/admin.css">
</head>
<body class="layout-fluid">
    <div class="page">
        <header class="navbar navbar-expand-md d-print-none">
            <div class="container-xl">
                <h1 class="navbar-brand navbar-brand-autodark m-0">
                    <a href="/kirpi/admin-demo" class="text-reset text-decoration-none">Kirpi Runtime</a>
                </h1>
                <div class="navbar-nav flex-row order-md-last">
                    <a class="nav-link px-2" href="/kirpi/ui-kit">UI Kit</a>
                    <a class="nav-link px-2" href="/kirpi/notify-test">Notify</a>
                    <a class="nav-link px-2" href="/kirpi/api-notify-test">API Notify</a>
                </div>
            </div>
        </header>

        <div class="page-wrapper">
            <div class="page-header d-print-none border-bottom">
                <div class="container-xl">
                    <div class="row g-2 align-items-center">
                        <div class="col">
                            <h2 class="page-title mb-1"><?= htmlspecialchars((string) ($heroTitle ?? 'Kirpi Admin UI Kit'), ENT_QUOTES, 'UTF-8') ?></h2>
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

    <script src="https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/js/tabler.min.js"></script>
    <?php require __DIR__ . '/partials/notify.php'; ?>
</body>
</html>
