<?php

declare(strict_types=1);

namespace Core\Frontend;

use Core\Http\Response;

class AdminUiController
{
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

        $html = $this->render('admin/layout', [
            'title' => 'Kirpi Admin UI Kit',
            'content' => $content,
        ]);

        return Response::make($html, 200, ['Content-Type' => 'text/html; charset=utf-8']);
    }

    public function demo(): Response
    {
        $templatePath = BASE_PATH . '/public/vendor/tabler/layout-fluid.html';
        if (!is_file($templatePath)) {
            return Response::make('Tabler layout-fluid template bulunamadi.', 500, ['Content-Type' => 'text/plain; charset=utf-8']);
        }

        $html = (string) file_get_contents($templatePath);
        $html = str_replace(
            '<title>Dashboard - Tabler - Premium and Open Source dashboard template with responsive and high quality UI.</title>',
            '<title>Kirpi Admin Demo - Tabler Layout Fluid</title>',
            $html
        );
        $html = str_replace('href="./dist/', 'href="/vendor/tabler/dist/', $html);
        $html = str_replace('src="./dist/', 'src="/vendor/tabler/dist/', $html);
        $html = str_replace('href="./preview/', 'href="/vendor/tabler/preview/', $html);
        $html = str_replace('src="./preview/', 'src="/vendor/tabler/preview/', $html);
        $html = str_replace('href="./favicon.ico"', 'href="/vendor/tabler/favicon.ico"', $html);
        $html = str_replace('href="."', 'href="/kirpi/admin-demo"', $html);
        $html = str_replace('href="?theme=dark"', 'href="/kirpi/admin-demo?theme=dark"', $html);
        $html = str_replace('href="?theme=light"', 'href="/kirpi/admin-demo?theme=light"', $html);
        $html = $this->replaceBetweenMarkers($html, '<!-- BEGIN NAVBAR  -->', '<!-- END NAVBAR  -->', $this->kirpiNavbar());
        $html = $this->replaceBetweenMarkers($html, '<!-- BEGIN PAGE HEADER -->', '<!-- END PAGE HEADER -->', $this->dummyPageHeader());
        $html = $this->replaceBetweenMarkers($html, '<!-- BEGIN PAGE BODY -->', '<!-- END PAGE BODY -->', $this->dummyPageBody());
        $html = $this->replaceBetweenMarkers($html, '<!--  BEGIN FOOTER  -->', '<!--  END FOOTER  -->', $this->kirpiFooter());
        $html = $this->replaceBetweenMarkers($html, '<!-- BEGIN PAGE SCRIPTS -->', '<!-- END PAGE SCRIPTS -->', "    <!-- BEGIN PAGE SCRIPTS -->\n    <!-- END PAGE SCRIPTS -->");
        $html = $this->removeThemeBuilderAndModals($html);

        return Response::make($html, 200, ['Content-Type' => 'text/html; charset=utf-8']);
    }

    private function dummyPageHeader(): string
    {
        return <<<'HTML'
        <!-- BEGIN PAGE HEADER -->
        <div class="page-header d-print-none" aria-label="Page header">
          <div class="container-xl">
            <div class="row g-2 align-items-center">
              <div class="col">
                <div class="page-pretitle">Kirpi</div>
                <h2 class="page-title">Dashboard</h2>
              </div>
            </div>
          </div>
        </div>
        <!-- END PAGE HEADER -->
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
                    <div class="subheader">Toplam Teklif</div>
                    <div class="h1 mb-0 mt-1">42</div>
                  </div>
                </div>
              </div>
              <div class="col-12 col-md-6 col-xl-3">
                <div class="card">
                  <div class="card-body">
                    <div class="subheader">Aktif Musteri</div>
                    <div class="h1 mb-0 mt-1">18</div>
                  </div>
                </div>
              </div>
              <div class="col-12 col-md-6 col-xl-3">
                <div class="card">
                  <div class="card-body">
                    <div class="subheader">Onay Orani</div>
                    <div class="h1 mb-0 mt-1">%63</div>
                  </div>
                </div>
              </div>
              <div class="col-12 col-md-6 col-xl-3">
                <div class="card">
                  <div class="card-body">
                    <div class="subheader">Bekleyen Is</div>
                    <div class="h1 mb-0 mt-1">7</div>
                  </div>
                </div>
              </div>

              <div class="col-12 col-lg-7">
                <div class="card">
                  <div class="card-header"><h3 class="card-title">Son Teklifler (Dummy)</h3></div>
                  <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                      <thead>
                        <tr><th>Kod</th><th>Baslik</th><th>Durum</th><th>Tarih</th></tr>
                      </thead>
                      <tbody>
                        <tr><td>T-001</td><td>Mart Paket Teklifi</td><td><span class="badge bg-green-lt">Aktif</span></td><td>2026-03-21</td></tr>
                        <tr><td>T-002</td><td>Restoran Maliyet Paketi</td><td><span class="badge bg-secondary-lt">Taslak</span></td><td>2026-03-20</td></tr>
                        <tr><td>T-003</td><td>CMS Gelisim Sprinti</td><td><span class="badge bg-orange-lt">Beklemede</span></td><td>2026-03-18</td></tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>

              <div class="col-12 col-lg-5">
                <div class="card">
                  <div class="card-header"><h3 class="card-title">Hizli Not (Dummy)</h3></div>
                  <div class="card-body">
                    <p class="text-secondary mb-3">Bu alan gelistirme asamasinda dummy icerik gostermek icin birakildi.</p>
                    <button class="btn btn-primary" type="button">Yeni Teklif</button>
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

    private function kirpiNavbar(): string
    {
        return <<<'HTML'
      <!-- BEGIN NAVBAR  -->
      <header class="navbar navbar-expand-md d-print-none">
        <div class="container-xl">
          <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#navbar-menu"
            aria-controls="navbar-menu"
            aria-expanded="false"
            aria-label="Toggle navigation"
          >
            <span class="navbar-toggler-icon"></span>
          </button>
          <div class="navbar-brand navbar-brand-autodark pe-0 pe-md-3">
            <a href="/kirpi/admin-demo" class="text-reset text-decoration-none fw-bold">Kirpi Framework</a>
          </div>
          <div class="navbar-nav flex-row order-md-last">
            <div class="nav-item">
              <a href="/kirpi/admin-demo?theme=dark" class="nav-link px-0 hide-theme-dark" title="Enable dark mode">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-1">
                  <path d="M12 3c.132 0 .263 0 .393 0a7.5 7.5 0 0 0 7.92 12.446a9 9 0 1 1 -8.313 -12.454z" />
                </svg>
              </a>
              <a href="/kirpi/admin-demo?theme=light" class="nav-link px-0 hide-theme-light" title="Enable light mode">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-1">
                  <path d="M12 12m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" />
                  <path d="M3 12h1m8 -9v1m8 8h1m-9 8v1m-6.4 -15.4l.7 .7m12.1 -.7l-.7 .7m0 11.4l.7 .7m-12.1 -.7l-.7 .7" />
                </svg>
              </a>
            </div>
          </div>
        </div>
      </header>
      <header class="navbar-expand-md">
        <div class="collapse navbar-collapse" id="navbar-menu">
          <div class="navbar">
            <div class="container-xl">
              <ul class="navbar-nav">
                <li class="nav-item active"><a class="nav-link" href="/kirpi/admin-demo"><span class="nav-link-title">Dashboard</span></a></li>
                <li class="nav-item"><a class="nav-link" href="/kirpi/ui-kit"><span class="nav-link-title">UI Kit</span></a></li>
                <li class="nav-item"><a class="nav-link" href="/kirpi/notify-test"><span class="nav-link-title">Notify Test</span></a></li>
                <li class="nav-item"><a class="nav-link" href="/kirpi/api-notify-test"><span class="nav-link-title">API Notify Test</span></a></li>
              </ul>
            </div>
          </div>
        </div>
      </header>
      <!-- END NAVBAR  -->
HTML;
    }

    private function removeThemeBuilderAndModals(string $html): string
    {
        $html = (string) preg_replace('/<!-- BEGIN PAGE MODALS -->.*?<!-- END PAGE MODALS -->/s', '', $html);
        return (string) preg_replace('/<div class="settings">.*?<\/form>\s*<\/div>/s', '', $html);
    }

    private function kirpiFooter(): string
    {
        return <<<'HTML'
        <!--  BEGIN FOOTER  -->
        <footer class="footer footer-transparent d-print-none">
          <div class="container-xl">
            <div class="row text-center align-items-center">
              <div class="col-12">
                <ul class="list-inline mb-0">
                  <li class="list-inline-item">
                    Copyright &copy; 2026
                    <a href="/kirpi/admin-demo" class="link-secondary">Kirpi Framework</a>. All rights reserved.
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </footer>
        <!--  END FOOTER  -->
HTML;
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

        $html = $this->render('admin/layout', [
            'title' => 'Kirpi Notify Test',
            'heroTitle' => 'Kirpi Notify Test',
            'heroSubtitle' => 'Backend flash/session mesajlarini toast katmaninda dogrulama sayfasi.',
            'content' => $content,
        ]);

        return Response::make($html, 200, ['Content-Type' => 'text/html; charset=utf-8']);
    }

    public function apiNotifyTest(): Response
    {
        $content = $this->render('admin/api-notify-test');

        $html = $this->render('admin/layout', [
            'title' => 'Kirpi API Notify Test',
            'heroTitle' => 'Kirpi API Notify Test',
            'heroSubtitle' => 'API response -> notify otomatik haritalama dogrulama sayfasi.',
            'content' => $content,
        ]);

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
