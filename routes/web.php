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
    .landing-card {
      position: relative;
      z-index: 2;
      width: min(560px, calc(100% - 2rem));
      box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08);
      backdrop-filter: blur(2px);
    }
  </style>
</head>
<body>
  <main class="landing-wrap">
    <canvas id="landingCanvas" class="landing-canvas"></canvas>
    <section class="card landing-card">
      <div class="card-body py-5 text-center">
        <h2 class="mb-0">Kirpi Framework</h2>
      </div>
    </section>
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
        speedMin: 0.15,
        speedMax: 0.55,
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

foreach (glob(base_path('modules/*/routes/web.php')) ?: [] as $moduleRouteFile) {
    /** @var string $moduleRouteFile */
    require $moduleRouteFile;
}
