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
        $html = $this->replaceBetweenMarkers($html, '<!-- BEGIN PAGE HEADER -->', '<!-- END PAGE HEADER -->', $this->dummyPageHeader());
        $html = $this->replaceBetweenMarkers($html, '<!-- BEGIN PAGE BODY -->', '<!-- END PAGE BODY -->', $this->dummyPageBody());
        $html = $this->replaceBetweenMarkers($html, '<!-- BEGIN PAGE SCRIPTS -->', '<!-- END PAGE SCRIPTS -->', "    <!-- BEGIN PAGE SCRIPTS -->\n    <!-- END PAGE SCRIPTS -->");

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
