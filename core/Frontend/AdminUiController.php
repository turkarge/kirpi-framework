<?php

declare(strict_types=1);

namespace Core\Frontend;

use Core\Frontend\Tabler\LayoutParts;
use Core\Frontend\Tabler\LayoutTransformer;
use Core\Http\Response;

class AdminUiController
{
    private ?LayoutParts $layoutParts = null;
    private ?LayoutTransformer $layoutTransformer = null;

    public function kit(): Response
    {
        $content = $this->render('admin/ui-kit', [
            'buttonPrimary' => $this->render('admin/components/button', [
                'label' => 'Kaydet',
                'variant' => 'primary',
            ]),
            'buttonGhost' => $this->render('admin/components/button', [
                'label' => 'Iptal',
                'variant' => 'ghost',
            ]),
            'card' => $this->render('admin/components/card', [
                'title' => 'Aylik Ozet',
                'body' => 'Bu kart, kritik metrikleri sade sekilde ozetlemek icin kullanilir.',
            ]),
            'form' => $this->render('admin/components/form'),
            'table' => $this->render('admin/components/table'),
        ]);

        $html = $this->renderTablerPage(
            title: 'Kirpi Admin UI Kit',
            heroTitle: 'Kirpi Admin UI Kit',
            heroSubtitle: 'Tabler tema standardi ile cekirdek UI bilesenleri.',
            content: $content,
            currentPath: '/kirpi/ui-kit'
        );

        return Response::make($html, 200, ['Content-Type' => 'text/html; charset=utf-8']);
    }

    public function demo(): Response
    {
        $html = $this->loadTablerShell();
        if ($html === null) {
            return Response::make('Tabler template bulunamadi.', 500, ['Content-Type' => 'text/plain; charset=utf-8']);
        }

        $html = $this->transformer()->normalizeTablerPaths($html);
        $html = $this->transformer()->applyTablerShellPatches($html, '/kirpi/admin-demo');
        $html = str_replace(
            '<title>Dashboard - Tabler - Premium and Open Source dashboard template with responsive and high quality UI.</title>',
            '<title>Kirpi Admin Demo - Tabler Layout Fluid</title>',
            $html
        );
        $html = $this->replaceBetweenMarkers(
            $html,
            '<!-- BEGIN PAGE HEADER -->',
            '<!-- END PAGE HEADER -->',
            $this->parts()->pageHeader(
                title: 'Core Control Center',
                subtitle: 'Kisisel uygulamalar icin sade, test edilebilir ve surdurulebilir framework cekirdegi.',
                actionsHtml: $this->pageHeaderActions('/kirpi/admin-demo')
            )
        );
        $html = $this->replaceBetweenMarkers($html, '<!-- BEGIN PAGE BODY -->', '<!-- END PAGE BODY -->', $this->dummyPageBody());
        $html = $this->replaceBetweenMarkers($html, '<!--  BEGIN FOOTER  -->', '<!--  END FOOTER  -->', $this->parts()->footer());
        $html = $this->replaceBetweenMarkers($html, '<!-- BEGIN PAGE SCRIPTS -->', '<!-- END PAGE SCRIPTS -->', "    <!-- BEGIN PAGE SCRIPTS -->\n    <!-- END PAGE SCRIPTS -->");
        $html = $this->transformer()->stripThemeBuilderAndModals($html);
        $html = $this->injectBeforeClosingTag($html, '</head>', $this->pwaHeadTags());
        $html = $this->injectBeforeClosingTag(
            $html,
            '</body>',
            $this->themePreferenceScript() . "\n" . $this->pwaRuntimeScript() . "\n" . $this->render('admin/partials/modal') . "\n" . $this->render('admin/partials/accessibility')
        );

        return Response::make($html, 200, ['Content-Type' => 'text/html; charset=utf-8']);
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
            actionsHtml: $this->pageHeaderActions($currentPath)
        );
        $body = $this->parts()->pageBody($content);

        $html = $this->replaceBetweenMarkers($html, '<!-- BEGIN PAGE HEADER -->', '<!-- END PAGE HEADER -->', $hero);
        $html = $this->replaceBetweenMarkers($html, '<!-- BEGIN PAGE BODY -->', '<!-- END PAGE BODY -->', $body);
        $html = $this->replaceBetweenMarkers($html, '<!--  BEGIN FOOTER  -->', '<!--  END FOOTER  -->', $this->parts()->footer());
        $html = $this->replaceBetweenMarkers($html, '<!-- BEGIN PAGE SCRIPTS -->', '<!-- END PAGE SCRIPTS -->', "    <!-- BEGIN PAGE SCRIPTS -->\n    <!-- END PAGE SCRIPTS -->");
        $html = $this->transformer()->stripThemeBuilderAndModals($html);

        $html = $this->injectBeforeClosingTag($html, '</head>', "  <link rel=\"stylesheet\" href=\"/assets/admin.css\">\n" . $this->pwaHeadTags());
        $html = $this->injectBeforeClosingTag(
            $html,
            '</body>',
            $this->themePreferenceScript() . "\n" . $this->pwaRuntimeScript() . "\n" . $this->render('admin/partials/notify') . "\n" . $this->render('admin/partials/modal') . "\n" . $this->render('admin/partials/accessibility')
        );

        return $html;
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

  const saveTheme = (theme) => {
    if (theme !== 'dark' && theme !== 'light') return;
    try {
      localStorage.setItem(KEY, theme);
      localStorage.setItem(LEGACY_KEY, theme);
    } catch (_e) {}
  };

  const params = new URLSearchParams(window.location.search);
  const queryTheme = params.get('theme');
  if (queryTheme === 'dark' || queryTheme === 'light') {
    saveTheme(queryTheme);
    applyTheme(queryTheme);
  } else {
    let savedTheme = null;
    try {
      savedTheme = localStorage.getItem(KEY) || localStorage.getItem(LEGACY_KEY);
    } catch (_e) {}
    if (savedTheme === 'dark' || savedTheme === 'light') {
      applyTheme(savedTheme);
    }
  }

  document.querySelectorAll('a[href*="?theme=dark"], a[href*="?theme=light"]').forEach((link) => {
    link.addEventListener('click', () => {
      const href = link.getAttribute('href') || '';
      const nextTheme = href.includes('theme=dark') ? 'dark' : (href.includes('theme=light') ? 'light' : null);
      if (nextTheme) {
        saveTheme(nextTheme);
      }
    });
  });
})();
</script>
HTML;
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

    private function transformer(): LayoutTransformer
    {
        if ($this->layoutTransformer === null) {
            $this->layoutTransformer = new LayoutTransformer();
        }

        return $this->layoutTransformer;
    }

    private function parts(): LayoutParts
    {
        if ($this->layoutParts === null) {
            $this->layoutParts = new LayoutParts();
        }

        return $this->layoutParts;
    }

    private function pageHeaderActions(string $currentPath): string
    {
        return match ($currentPath) {
            '/kirpi/ui-kit' => '<a href="/kirpi/admin-demo" class="btn btn-1">Dashboard</a><a href="/kirpi/notify-test" class="btn btn-primary btn-5">Notify Test</a>',
            '/kirpi/notify-test' => '<a href="/kirpi/admin-demo" class="btn btn-1">Dashboard</a><a href="/kirpi/api-notify-test" class="btn btn-primary btn-5">API Notify Test</a>',
            '/kirpi/api-notify-test' => '<a href="/kirpi/admin-demo" class="btn btn-1">Dashboard</a><a href="/kirpi/notify-test" class="btn btn-primary btn-5">Notify Test</a>',
            '/kirpi/pwa-test' => '<a href="/kirpi/admin-demo" class="btn btn-1">Dashboard</a><a href="/kirpi/ui-kit" class="btn btn-primary btn-5">UI Kit</a>',
            '/kirpi/modal-test' => '<a href="/kirpi/admin-demo" class="btn btn-1">Dashboard</a><a href="/kirpi/ui-kit" class="btn btn-primary btn-5">UI Kit</a>',
            '/kirpi/import-export-test' => '<a href="/kirpi/admin-demo" class="btn btn-1">Dashboard</a><a href="/kirpi/ui-kit" class="btn btn-primary btn-5">UI Kit</a>',
            '/kirpi/state-test' => '<a href="/kirpi/admin-demo" class="btn btn-1">Dashboard</a><a href="/kirpi/ui-kit" class="btn btn-primary btn-5">UI Kit</a>',
            '/kirpi/a11y-test' => '<a href="/kirpi/admin-demo" class="btn btn-1">Dashboard</a><a href="/kirpi/ui-kit" class="btn btn-primary btn-5">UI Kit</a>',
            default => '<a href="/kirpi/ui-kit" class="btn btn-1">UI Kit</a><a href="/kirpi/notify-test" class="btn btn-primary btn-5">Notify Test</a>',
        };
    }

    private function pwaHeadTags(): string
    {
        return <<<'HTML'
  <link rel="manifest" href="/manifest.webmanifest">
  <meta name="theme-color" content="#1f2937">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="default">
  <meta name="apple-mobile-web-app-title" content="Kirpi">
HTML;
    }

    private function pwaRuntimeScript(): string
    {
        return <<<'HTML'
<script>
(() => {
  if (!('serviceWorker' in navigator)) return;

  window.addEventListener('load', () => {
    navigator.serviceWorker.register('/sw.js').catch(() => {});
  });

  let deferredPrompt = null;
  window.addEventListener('beforeinstallprompt', (event) => {
    event.preventDefault();
    deferredPrompt = event;
    window.dispatchEvent(new CustomEvent('kirpi:pwa-install-ready'));
  });

  document.addEventListener('click', async (event) => {
    const source = event.target instanceof Element ? event.target.closest('[data-kirpi-pwa-install]') : null;
    if (!source || !deferredPrompt) return;
    deferredPrompt.prompt();
    try {
      await deferredPrompt.userChoice;
    } catch (_e) {}
    deferredPrompt = null;
  });
})();
</script>
HTML;
    }

    private function dummyPageBody(): string
    {
        return <<<'HTML'
        <!-- BEGIN PAGE BODY -->
        <div class="page-body">
          <div class="container-xl">
            <div class="row row-deck row-cards">
              <div class="col-12 col-md-6 col-xl-3">
                <div class="card">
                  <div class="card-body">
                    <div class="subheader">Routing</div>
                    <div class="h1 mb-0 mt-1 text-green">Stable</div>
                    <div class="text-secondary mt-1">Deterministik route kayit ve middleware zinciri.</div>
                  </div>
                </div>
              </div>
              <div class="col-12 col-md-6 col-xl-3">
                <div class="card">
                  <div class="card-body">
                    <div class="subheader">HTTP Layer</div>
                    <div class="h1 mb-0 mt-1 text-green">Ready</div>
                    <div class="text-secondary mt-1">Request/Response davranisi sade API ile ilerliyor.</div>
                  </div>
                </div>
              </div>
              <div class="col-12 col-md-6 col-xl-3">
                <div class="card">
                  <div class="card-body">
                    <div class="subheader">Database</div>
                    <div class="h1 mb-0 mt-1 text-green">Connected</div>
                    <div class="text-secondary mt-1">Migration, query builder ve driver katmani aktif.</div>
                  </div>
                </div>
              </div>
              <div class="col-12 col-md-6 col-xl-3">
                <div class="card">
                  <div class="card-body">
                    <div class="subheader">Observability</div>
                    <div class="h1 mb-0 mt-1 text-green">Enabled</div>
                    <div class="text-secondary mt-1">Health, runtime self-check ve test akislarinda dogrulama.</div>
                  </div>
                </div>
              </div>

              <div class="col-12 col-lg-7">
                <div class="card">
                  <div class="card-header"><h3 class="card-title">Kirpi Core Checklist</h3></div>
                  <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                      <thead>
                        <tr><th>Alan</th><th>Durum</th><th>Not</th><th>Oncelik</th></tr>
                      </thead>
                      <tbody>
                        <tr><td>Application Lifecycle</td><td><span class="badge bg-green-lt">Tamam</span></td><td>Bootstrap akis netlesti</td><td>Yuksek</td></tr>
                        <tr><td>Container / DI</td><td><span class="badge bg-green-lt">Tamam</span></td><td>Temel resolve ve singleton davranisi testli</td><td>Yuksek</td></tr>
                        <tr><td>Validation</td><td><span class="badge bg-green-lt">Tamam</span></td><td>Kritik kurallar unit test ile guvencede</td><td>Yuksek</td></tr>
                        <tr><td>Auth / Authorization</td><td><span class="badge bg-yellow-lt">Iterasyon</span></td><td>Policy odakli sade API toparlaniyor</td><td>Orta</td></tr>
                        <tr><td>Frontend Shell</td><td><span class="badge bg-green-lt">Hazir</span></td><td>Tabler tabanli kirpi-layout baglandi</td><td>Orta</td></tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>

              <div class="col-12 col-lg-5">
                <div class="card">
                  <div class="card-header"><h3 class="card-title">Hizli Eylemler</h3></div>
                  <div class="card-body">
                    <p class="text-secondary mb-3">Gelistirme sirasinda framework davranisini dogrulamak icin bu test noktalarini kullan.</p>
                    <div class="d-grid gap-2">
                      <a class="btn btn-primary" href="/kirpi/notify-test">Backend Flash / Notify</a>
                      <a class="btn btn-1" href="/kirpi/api-notify-test">API Notify Bridge</a>
                      <a class="btn btn-1" href="/health">Health Endpoint</a>
                      <a class="btn btn-1" href="/monitor/self-check">Runtime Self Check</a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- END PAGE BODY -->
HTML;
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


    public function notifyTest(\Core\Http\Request $request): Response
    {
        $kind = strtolower($this->query($request, 'kind'));

        if (in_array($kind, ['success', 'info', 'warning', 'error'], true)) {
            flash(
                message: "Flash mesaji olustu: {$kind}",
                level: $kind,
                title: 'Backend Flash'
            );
        }

        $content = $this->render('admin/notify-test', [
            'kind' => $kind,
        ]);

        $html = $this->renderTablerPage(
            title: 'Kirpi Notify Test',
            heroTitle: 'Kirpi Notify Test',
            heroSubtitle: 'Backend flash/session mesajlarini Tabler UI uzerinde dogrulama.',
            content: $content,
            currentPath: '/kirpi/notify-test'
        );

        return Response::make($html, 200, ['Content-Type' => 'text/html; charset=utf-8']);
    }

    public function apiNotifyTest(): Response
    {
        $content = $this->render('admin/api-notify-test');

        $html = $this->renderTablerPage(
            title: 'Kirpi API Notify Test',
            heroTitle: 'Kirpi API Notify Test',
            heroSubtitle: 'API response -> notify otomatik haritalama dogrulama sayfasi.',
            content: $content,
            currentPath: '/kirpi/api-notify-test'
        );

        return Response::make($html, 200, ['Content-Type' => 'text/html; charset=utf-8']);
    }

    public function pwaTest(): Response
    {
        $content = $this->render('admin/pwa-test');

        $html = $this->renderTablerPage(
            title: 'Kirpi PWA Test',
            heroTitle: 'Kirpi PWA Test',
            heroSubtitle: 'Manifest, service worker ve offline fallback dogrulama sayfasi.',
            content: $content,
            currentPath: '/kirpi/pwa-test'
        );

        return Response::make($html, 200, ['Content-Type' => 'text/html; charset=utf-8']);
    }

    public function modalTest(): Response
    {
        $content = $this->render('admin/modal-test');

        $html = $this->renderTablerPage(
            title: 'Kirpi Modal Test',
            heroTitle: 'Kirpi Modal Test',
            heroSubtitle: 'Merkezi modal API (window.kirpiModal) davranis dogrulama sayfasi.',
            content: $content,
            currentPath: '/kirpi/modal-test'
        );

        return Response::make($html, 200, ['Content-Type' => 'text/html; charset=utf-8']);
    }

    public function importExportTest(): Response
    {
        $content = $this->render('admin/import-export-test');

        $html = $this->renderTablerPage(
            title: 'Kirpi Import/Export Test',
            heroTitle: 'Kirpi Import/Export Test',
            heroSubtitle: 'CSV import preview ve export akisi dogrulama sayfasi.',
            content: $content,
            currentPath: '/kirpi/import-export-test'
        );

        return Response::make($html, 200, ['Content-Type' => 'text/html; charset=utf-8']);
    }

    public function stateTest(): Response
    {
        $content = $this->render('admin/state-test');

        $html = $this->renderTablerPage(
            title: 'Kirpi State Test',
            heroTitle: 'Kirpi State Test',
            heroSubtitle: 'Empty / Loading / Error durum bilesenlerinin dogrulama sayfasi.',
            content: $content,
            currentPath: '/kirpi/state-test'
        );

        return Response::make($html, 200, ['Content-Type' => 'text/html; charset=utf-8']);
    }

    public function a11yTest(): Response
    {
        $content = $this->render('admin/a11y-test');

        $html = $this->renderTablerPage(
            title: 'Kirpi A11y Test',
            heroTitle: 'Kirpi A11y Test',
            heroSubtitle: 'Klavye kisayollari ve temel erisilebilirlik davranislari dogrulama sayfasi.',
            content: $content,
            currentPath: '/kirpi/a11y-test'
        );

        return Response::make($html, 200, ['Content-Type' => 'text/html; charset=utf-8']);
    }

    public function apiNotifySample(\Core\Http\Request $request): Response
    {
        $case = strtolower($this->query($request, 'case'));

        return match ($case) {
            'success' => Response::json([
                'message' => 'Kayit basariyla olusturuldu.',
                'level' => 'success',
            ]),
            'info' => Response::json([
                'message' => 'Degisiklik bulunamadi.',
                'level' => 'info',
            ]),
            'warning' => Response::json([
                'errors' => [
                    'cost' => ['Maliyet alani zorunludur.'],
                ],
            ], 422),
            'error' => Response::json([
                'error' => 'Servis gecici olarak kullanilamiyor.',
            ], 500),
            'custom' => Response::json([
                'notify' => [
                    'level' => 'warning',
                    'title' => 'Quota',
                    'message' => 'Gunluk limit %90 seviyesine ulasti.',
                ],
            ]),
            default => Response::json([
                'message' => 'Bilinmeyen test senaryosu.',
                'level' => 'info',
            ]),
        };
    }

    private function query(\Core\Http\Request $request, string $key): string
    {
        $value = (string) $request->get($key, '');
        if ($value !== '') {
            return $value;
        }

        $query = [];
        parse_str((string) parse_url($request->uri(), PHP_URL_QUERY), $query);

        return (string) ($query[$key] ?? '');
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
