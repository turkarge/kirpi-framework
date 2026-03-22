<?php

declare(strict_types=1);

namespace Core\Monitor;

use Core\Frontend\Tabler\LayoutParts;
use Core\Frontend\Tabler\LayoutTransformer;
use Core\Http\Request;
use Core\Http\Response;
use Core\Routing\Router;

class MonitorController
{
    private ?LayoutParts $layoutParts = null;
    private ?LayoutTransformer $layoutTransformer = null;

    public function __construct(
        private readonly HealthChecker $health,
        private readonly MetricsCollector $metrics,
    ) {}

    public function dashboard(Request $request): Response
    {
        if (!$this->isAuthorized($request)) {
            return $this->unauthorized();
        }

        $content = $this->render('dashboard', [
            'token' => $this->extractToken($request),
        ]);

        $html = $this->renderTablerPage(
            title: 'Kirpi Monitor',
            heroTitle: 'Kirpi Monitor',
            heroSubtitle: 'Health, metrics, logs ve route gozlemi.',
            content: $content,
            currentPath: '/kirpi-monitor'
        );

        return Response::make($html, 200, ['Content-Type' => 'text/html; charset=utf-8']);
    }

    public function health(Request $request): Response
    {
        if (!$this->isAuthorized($request)) {
            return Response::json(['error' => 'Unauthorized'], 401);
        }

        return Response::json($this->health->check());
    }

    public function metrics(Request $request): Response
    {
        if (!$this->isAuthorized($request)) {
            return Response::json(['error' => 'Unauthorized'], 401);
        }

        return Response::json($this->metrics->collect());
    }

    public function logs(Request $request): Response
    {
        if (!$this->isAuthorized($request)) {
            return Response::json(['error' => 'Unauthorized'], 401);
        }

        $lines = max(1, min((int) $request->get('lines', 50), 300));
        $level = (string) $request->get('level', '');

        return Response::json(['logs' => $this->getRecentLogs($lines, $level)]);
    }

    public function routes(Request $request): Response
    {
        if (!$this->isAuthorized($request)) {
            return Response::json(['error' => 'Unauthorized'], 401);
        }

        /** @var Router $router */
        $router = app(Router::class);
        $collection = $router->getRoutes()->all();

        $routes = array_map(static fn ($route): array => [
            'methods' => $route->getMethods(),
            'uri' => $route->getUri(),
            'name' => $route->getName(),
            'middlewares' => $route->getMiddlewares(),
        ], $collection);

        return Response::json([
            'total' => count($routes),
            'routes' => $routes,
        ]);
    }

    public function info(Request $request): Response
    {
        if (!$this->isAuthorized($request)) {
            return Response::json(['error' => 'Unauthorized'], 401);
        }

        return Response::json([
            'framework' => 'Kirpi Framework',
            'version' => config('app.version', '1.0.0'),
            'php' => PHP_VERSION,
            'env' => config('app.env', 'local'),
            'debug' => config('app.debug', false),
            'locale' => config('app.locale', 'tr'),
            'timezone' => config('app.timezone', 'Europe/Istanbul'),
            'uptime' => $this->getUptime(),
            'timestamp' => date('Y-m-d H:i:s'),
        ]);
    }

    public function snapshot(Request $request): Response
    {
        if (!$this->isAuthorized($request)) {
            return Response::json(['error' => 'Unauthorized'], 401);
        }

        return Response::json([
            'status' => 'ok',
            'health' => $this->health->check(),
            'metrics' => $this->metrics->collect(),
            'info' => [
                'framework' => 'Kirpi Framework',
                'version' => config('app.version', '1.0.0'),
                'php' => PHP_VERSION,
                'env' => config('app.env', 'local'),
                'debug' => config('app.debug', false),
                'timestamp' => date('Y-m-d H:i:s'),
            ],
        ]);
    }

    private function renderTablerPage(string $title, string $heroTitle, string $heroSubtitle, string $content, string $currentPath): string
    {
        $html = $this->loadTablerShell();
        if ($html === null) {
            return 'Tabler template bulunamadi.';
        }

        $html = $this->transformer()->normalizeTablerPaths($html);
        $html = $this->transformer()->applyTablerShellPatches($html, $currentPath);
        $html = (string) preg_replace('/<title>.*?<\/title>/si', '<title>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</title>', $html, 1);

        $hero = $this->parts()->pageHeader(
            title: $heroTitle,
            subtitle: $heroSubtitle,
            actionsHtml: '<a href="/kirpi/admin-demo" class="btn btn-1">Dashboard</a><button type="button" class="btn btn-primary" id="monitor-refresh">Yenile</button>'
        );
        $body = $this->parts()->pageBody($content);

        $html = $this->replaceBetweenMarkers($html, '<!-- BEGIN PAGE HEADER -->', '<!-- END PAGE HEADER -->', $hero);
        $html = $this->replaceBetweenMarkers($html, '<!-- BEGIN PAGE BODY -->', '<!-- END PAGE BODY -->', $body);
        $html = $this->replaceBetweenMarkers($html, '<!--  BEGIN FOOTER  -->', '<!--  END FOOTER  -->', $this->parts()->footer());
        $html = $this->replaceBetweenMarkers($html, '<!-- BEGIN PAGE SCRIPTS -->', '<!-- END PAGE SCRIPTS -->', "    <!-- BEGIN PAGE SCRIPTS -->\n    <!-- END PAGE SCRIPTS -->");
        $html = $this->transformer()->stripThemeBuilderAndModals($html);

        $html = $this->injectBeforeClosingTag($html, '</head>', "  <link rel=\"stylesheet\" href=\"/assets/admin.css\">");
        $html = $this->injectBeforeClosingTag($html, '</body>', $this->themePreferenceScript());

        return $html;
    }

    private function loadTablerShell(): ?string
    {
        $templatePath = BASE_PATH . '/public/vendor/tabler/kirpi-layout.html';
        if (!is_file($templatePath)) {
            $templatePath = BASE_PATH . '/public/vendor/tabler/layout-fluid.html';
        }

        if (!is_file($templatePath)) {
            return null;
        }

        return (string) file_get_contents($templatePath);
    }

    private function parts(): LayoutParts
    {
        if ($this->layoutParts === null) {
            $this->layoutParts = new LayoutParts();
        }

        return $this->layoutParts;
    }

    private function transformer(): LayoutTransformer
    {
        if ($this->layoutTransformer === null) {
            $this->layoutTransformer = new LayoutTransformer();
        }

        return $this->layoutTransformer;
    }

    private function themePreferenceScript(): string
    {
        return <<<'HTML'
<script>
(() => {
  const KEY = 'kirpi.theme';
  const LEGACY_KEY = 'tabler-theme';
  const root = document.documentElement;

  const applyTheme = (theme) => {
    if (theme !== 'dark' && theme !== 'light') return;
    root.setAttribute('data-bs-theme', theme);
    if (theme === 'dark') {
      root.classList.add('theme-dark');
    } else {
      root.classList.remove('theme-dark');
    }
  };

  const params = new URLSearchParams(window.location.search);
  const queryTheme = params.get('theme');
  if (queryTheme === 'dark' || queryTheme === 'light') {
    try {
      localStorage.setItem(KEY, queryTheme);
      localStorage.setItem(LEGACY_KEY, queryTheme);
    } catch (_e) {}
    applyTheme(queryTheme);
    return;
  }

  let savedTheme = null;
  try {
    savedTheme = localStorage.getItem(KEY) || localStorage.getItem(LEGACY_KEY);
  } catch (_e) {}

  if (savedTheme === 'dark' || savedTheme === 'light') {
    applyTheme(savedTheme);
  }
})();
</script>
HTML;
    }

    private function isAuthorized(Request $request): bool
    {
        if (!(bool) env('MONITOR_ENABLED', true)) {
            return false;
        }

        $whitelist = trim((string) env('MONITOR_IP_WHITELIST', ''));
        if ($whitelist !== '') {
            $allowed = array_values(array_filter(array_map('trim', explode(',', $whitelist))));
            if (!in_array($request->ip(), $allowed, true)) {
                return false;
            }
        }

        $password = (string) env('MONITOR_PASSWORD', '');
        if ($password !== '') {
            $token = $this->extractToken($request);

            return $token !== '' && hash_equals($password, $token);
        }

        return true;
    }

    private function extractToken(Request $request): string
    {
        $token = (string) ($request->get('token') ?? '');
        if ($token !== '') {
            return $token;
        }

        $headerToken = (string) ($request->header('X-Monitor-Token') ?? '');
        if ($headerToken !== '') {
            return $headerToken;
        }

        return (string) ($request->bearerToken() ?? '');
    }

    private function unauthorized(): Response
    {
        return Response::make(
            '<h1>401 Unauthorized</h1><p>Monitor access denied.</p>',
            401,
            ['Content-Type' => 'text/html; charset=utf-8']
        );
    }

    /** @return array<int, array<string, string>> */
    private function getRecentLogs(int $lines, string $level): array
    {
        $logFiles = glob(storage_path('logs/*-app.log'));
        if (empty($logFiles)) {
            return [];
        }

        $logFile = end($logFiles);
        if (!is_string($logFile) || !is_file($logFile)) {
            return [];
        }

        $content = (string) file_get_contents($logFile);
        $allLines = array_values(array_filter(explode("\n", $content), static fn (string $line): bool => $line !== ''));
        $recentLines = array_slice($allLines, -$lines);

        $parsed = [];
        foreach ($recentLines as $line) {
            if (!preg_match('/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] (\w+)\.(\w+): (.+)/', $line, $m)) {
                continue;
            }

            if ($level !== '' && strtolower($m[3]) !== strtolower($level)) {
                continue;
            }

            $parsed[] = [
                'time' => $m[1],
                'channel' => $m[2],
                'level' => $m[3],
                'message' => $m[4],
            ];
        }

        return array_reverse($parsed);
    }

    private function getUptime(): string
    {
        if (is_file('/proc/uptime')) {
            $uptime = (int) file_get_contents('/proc/uptime');
            $days = floor($uptime / 86400);
            $hours = floor(($uptime % 86400) / 3600);
            $minutes = floor(($uptime % 3600) / 60);

            return "{$days}d {$hours}h {$minutes}m";
        }

        return 'N/A';
    }

    private function replaceBetweenMarkers(string $html, string $startMarker, string $endMarker, string $replacement): string
    {
        $start = strpos($html, $startMarker);
        $end = strpos($html, $endMarker);
        if ($start === false || $end === false || $end < $start) {
            return $html;
        }

        $end += strlen($endMarker);

        return substr($html, 0, $start) . $replacement . substr($html, $end);
    }

    private function injectBeforeClosingTag(string $html, string $closingTag, string $injection): string
    {
        $pos = strripos($html, $closingTag);
        if ($pos === false) {
            return $html;
        }

        return substr($html, 0, $pos) . $injection . "\n" . substr($html, $pos);
    }

    /** @param array<string, mixed> $data */
    private function render(string $view, array $data = []): string
    {
        extract($data, EXTR_SKIP);

        ob_start();
        require __DIR__ . '/templates/' . $view . '.php';

        return (string) ob_get_clean();
    }
}
