<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars((string) $title, ENT_QUOTES, 'UTF-8') ?></title>
    <style>
        :root {
            --ink: #102a43;
            --muted: #486581;
            --bg: #f4f7fb;
            --panel: #ffffff;
            --line: #d9e2ec;
            --brand: #1f7a8c;
            --brand-deep: #125d6a;
            --glow: #e0f2f1;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: "Space Grotesk", "Avenir Next", "Segoe UI", sans-serif;
            color: var(--ink);
            background: radial-gradient(circle at 5% 10%, var(--glow), var(--bg) 35%);
        }
        .shell { max-width: 1100px; margin: 0 auto; padding: 28px 20px 48px; }
        .hero {
            background: linear-gradient(120deg, #0f4c5c, var(--brand));
            border-radius: 18px;
            color: #fff;
            padding: 22px 24px;
            box-shadow: 0 14px 30px rgba(16, 42, 67, 0.15);
        }
        .hero h1 { margin: 0 0 6px; font-size: 30px; letter-spacing: -0.02em; }
        .hero p { margin: 0; opacity: 0.92; }
        .content { margin-top: 18px; display: grid; gap: 14px; }
        .panel { background: var(--panel); border: 1px solid var(--line); border-radius: 14px; padding: 16px; }
        .panel h2 { margin: 0 0 10px; font-size: 20px; }
        .inline { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }
        @media (max-width: 720px) {
            .hero h1 { font-size: 24px; }
        }
    </style>
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
</body>
</html>
