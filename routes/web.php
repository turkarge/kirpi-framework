<?php

declare(strict_types=1);

/** @var \Core\Routing\Router $router */

$router->get('/', function (): \Core\Http\Response {
    $html = <<<'HTML'
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kirpi Framework</title>
  <link rel="stylesheet" href="/vendor/tabler/dist/css/tabler.min.css">
  <style>
    body { min-height: 100vh; }
    .landing-wrap {
      min-height: 100vh;
      display: grid;
      place-items: center;
      background: radial-gradient(circle at top left, #f8fafc, #eef2f7 60%, #e8edf5);
    }
    .landing-card {
      width: min(560px, calc(100% - 2rem));
      box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08);
    }
  </style>
</head>
<body>
  <main class="landing-wrap">
    <section class="card landing-card">
      <div class="card-body py-5 text-center">
        <h2 class="mb-0">Kirpi Framework</h2>
      </div>
    </section>
  </main>
</body>
</html>
HTML;

    return \Core\Http\Response::make($html, 200, ['Content-Type' => 'text/html; charset=utf-8']);
});

$router->get('/health', function (): \Core\Http\Response {
    return \Core\Http\Response::json([
        'status' => 'healthy',
        'timestamp' => date('Y-m-d H:i:s'),
    ]);
});

$router->get('/ready', [\Core\Runtime\RuntimeController::class, 'ready']);

foreach (glob(base_path('modules/*/routes/web.php')) ?: [] as $moduleRouteFile) {
    /** @var string $moduleRouteFile */
    require $moduleRouteFile;
}
