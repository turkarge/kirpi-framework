<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars((string) $title, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="/assets/admin.css">
</head>
<body>
    <main class="shell">
        <section class="hero">
            <h1><?= htmlspecialchars((string) ($heroTitle ?? 'Kirpi Admin UI Kit'), ENT_QUOTES, 'UTF-8') ?></h1>
            <p><?= htmlspecialchars((string) ($heroSubtitle ?? 'Tekrar kullanilabilir admin bilesenleri icin temel gorunur alan.'), ENT_QUOTES, 'UTF-8') ?></p>
        </section>
        <section class="content">
            <?= $content ?>
        </section>
    </main>
    <?php require __DIR__ . '/partials/notify.php'; ?>
</body>
</html>
