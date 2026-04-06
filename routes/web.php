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
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Sansation:wght@300&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/vendor/tabler/dist/css/tabler.min.css">
  <style>
    html, body { width: 100%; height: 100%; margin: 0; }
    .landing-wrap {
      position: relative;
      min-height: 100%;
      display: grid;
      place-items: center;
      background: radial-gradient(circle at top left, #f8fafc, #eef2f7 60%, #e8edf5);
      overflow: hidden;
    }
    .landing-canvas {
      position: absolute;
      inset: 0;
      width: 100%;
      height: 100%;
    }
    .landing-content {
      position: relative;
      z-index: 2;
      text-align: center;
    }
    .landing-title {
      margin: 0;
      color: #0f172a;
      font-family: "Sansation", "Segoe UI", "Helvetica Neue", Arial, sans-serif;
      font-weight: 300;
      letter-spacing: 0.08em;
      font-size: clamp(2rem, 6vw, 4.2rem);
      text-transform: uppercase;
    }
    .landing-doc-link {
      margin-top: 1rem;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      color: #0f172a;
      border: 1px solid rgba(15, 23, 42, 0.25);
      border-radius: 999px;
      padding: 0.45rem 1rem;
      text-decoration: none;
      font-size: 0.9rem;
      letter-spacing: 0.03em;
      background: rgba(255, 255, 255, 0.65);
      transition: all 120ms ease-in-out;
    }
    .landing-doc-link:hover {
      color: #0b1220;
      border-color: rgba(15, 23, 42, 0.45);
      background: rgba(255, 255, 255, 0.9);
      transform: translateY(-1px);
    }
  </style>
</head>
<body>
  <main class="landing-wrap">
    <canvas id="landingCanvas" class="landing-canvas"></canvas>
    <div class="landing-content">
      <h2 class="landing-title">K&#304;RP&#304; FRAMEWORK</h2>
      <a class="landing-doc-link" href="https://github.com/turkarge/kirpi-framework/tree/main/docs" target="_blank" rel="noreferrer">
        Dokumantasyon
      </a>
    </div>
  </main>
  <script>
    (() => {
      const canvas = document.getElementById('landingCanvas');
      if (!(canvas instanceof HTMLCanvasElement)) return;

      const ctx = canvas.getContext('2d');
      if (!ctx) return;

      const config = {
        count: 90,
        maxDistance: 130,
        speedMin: 0.05,
        speedMax: 0.22,
      };

      const particles = [];
      const mouse = { x: null, y: null, radius: 140 };

      const random = (min, max) => Math.random() * (max - min) + min;

      const resize = () => {
        const dpr = window.devicePixelRatio || 1;
        canvas.width = Math.floor(window.innerWidth * dpr);
        canvas.height = Math.floor(window.innerHeight * dpr);
        canvas.style.width = window.innerWidth + 'px';
        canvas.style.height = window.innerHeight + 'px';
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
      };

      const createParticles = () => {
        particles.length = 0;
        for (let i = 0; i < config.count; i += 1) {
          particles.push({
            x: random(0, window.innerWidth),
            y: random(0, window.innerHeight),
            vx: random(config.speedMin, config.speedMax) * (Math.random() > 0.5 ? 1 : -1),
            vy: random(config.speedMin, config.speedMax) * (Math.random() > 0.5 ? 1 : -1),
            size: random(0.8, 1.9),
          });
        }
      };

      const update = () => {
        for (const p of particles) {
          p.x += p.vx;
          p.y += p.vy;

          if (p.x <= 0 || p.x >= window.innerWidth) p.vx *= -1;
          if (p.y <= 0 || p.y >= window.innerHeight) p.vy *= -1;

          if (mouse.x !== null && mouse.y !== null) {
            const dx = p.x - mouse.x;
            const dy = p.y - mouse.y;
            const distance = Math.sqrt(dx * dx + dy * dy);
            if (distance < mouse.radius && distance > 0) {
              const force = (mouse.radius - distance) / mouse.radius;
              p.x += (dx / distance) * force * 1.7;
              p.y += (dy / distance) * force * 1.7;
            }
          }
        }
      };

      const draw = () => {
        ctx.clearRect(0, 0, window.innerWidth, window.innerHeight);

        for (let i = 0; i < particles.length; i += 1) {
          const a = particles[i];
          ctx.beginPath();
          ctx.arc(a.x, a.y, a.size, 0, Math.PI * 2);
          ctx.fillStyle = 'rgba(30, 41, 59, 0.75)';
          ctx.fill();

          for (let j = i + 1; j < particles.length; j += 1) {
            const b = particles[j];
            const dx = a.x - b.x;
            const dy = a.y - b.y;
            const distance = Math.sqrt(dx * dx + dy * dy);
            if (distance <= config.maxDistance) {
              const opacity = 1 - (distance / config.maxDistance);
              ctx.beginPath();
              ctx.moveTo(a.x, a.y);
              ctx.lineTo(b.x, b.y);
              ctx.strokeStyle = `rgba(30, 41, 59, ${opacity * 0.35})`;
              ctx.lineWidth = 1;
              ctx.stroke();
            }
          }
        }
      };

      const tick = () => {
        update();
        draw();
        requestAnimationFrame(tick);
      };

      window.addEventListener('mousemove', (event) => {
        mouse.x = event.clientX;
        mouse.y = event.clientY;
      });
      window.addEventListener('mouseleave', () => {
        mouse.x = null;
        mouse.y = null;
      });
      window.addEventListener('resize', () => {
        resize();
        createParticles();
      });

      resize();
      createParticles();
      tick();
    })();
  </script>
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

$router->get('/login', [\Core\Auth\WebAuthController::class, 'showLogin'])->middleware('guest');
$router->post('/login', [\Core\Auth\WebAuthController::class, 'login'])->middleware('guest');
$router->get('/forgot-password', [\Core\Auth\WebAuthController::class, 'showForgotPassword'])->middleware('guest');
$router->post('/forgot-password', [\Core\Auth\WebAuthController::class, 'forgotPassword'])->middleware('guest');
$router->get('/tos', [\Core\Auth\WebAuthController::class, 'termsOfService']);
$router->get('/dashboard', [\Core\Auth\WebAuthController::class, 'dashboard'])->middleware('auth');
$router->get('/lock', [\Core\Auth\WebAuthController::class, 'showLockScreen']);
$router->post('/lock', [\Core\Auth\WebAuthController::class, 'unlock']);
$router->get('/exit', [\Core\Auth\WebAuthController::class, 'logout']);
$router->post('/exit', [\Core\Auth\WebAuthController::class, 'logout']);

// Backward compatibility routes.
$router->get('/terms-of-service', [\Core\Auth\WebAuthController::class, 'termsOfService']);
$router->get('/lock-screen', [\Core\Auth\WebAuthController::class, 'showLockScreen']);
$router->post('/lock-screen', [\Core\Auth\WebAuthController::class, 'unlock']);
$router->post('/logout', [\Core\Auth\WebAuthController::class, 'logout']);

foreach (glob(base_path('modules/*/routes/web.php')) ?: [] as $moduleRouteFile) {
    /** @var string $moduleRouteFile */
    require $moduleRouteFile;
}
