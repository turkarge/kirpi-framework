<?php

declare(strict_types=1);

namespace Core\Frontend;

use Core\Frontend\ViewModels\AdminDemoViewModel;
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

        return Response::make($html, 200, ['Content-Type' => 'text/html; charset=utf-8']);
    }

    private function demoLegacy(): Response
    {
        $vm = (new AdminDemoViewModel())->toArray();

        $content = $this->render('admin/demo', [
            'kpiCardA' => $this->render('admin/components/card', [
                'title' => $vm['cards'][0]['title'],
                'body' => $vm['cards'][0]['body'],
            ]),
            'kpiCardB' => $this->render('admin/components/card', [
                'title' => $vm['cards'][1]['title'],
                'body' => $vm['cards'][1]['body'],
            ]),
            'quickForm' => $this->render('admin/components/form'),
            'latestTable' => $this->render('admin/components/table'),
            'saveButton' => $this->render('admin/components/button', [
                'label' => $vm['actions']['save'],
                'variant' => 'primary',
            ]),
            'filterButton' => $this->render('admin/components/button', [
                'label' => $vm['actions']['filter'],
                'variant' => 'ghost',
            ]),
        ]);

        $html = $this->render('admin/layout', [
            'title' => $vm['title'],
            'heroTitle' => $vm['heroTitle'],
            'heroSubtitle' => $vm['heroSubtitle'],
            'content' => $content,
        ]);

        return Response::make($html, 200, ['Content-Type' => 'text/html; charset=utf-8']);
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
