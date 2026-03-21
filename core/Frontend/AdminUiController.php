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
        $content = $this->render('admin/demo', [
            'kpiCardA' => $this->render('admin/components/card', [
                'title' => 'Toplam Teklif',
                'body' => 'Bu ay 42 adet teklif olusturuldu.',
            ]),
            'kpiCardB' => $this->render('admin/components/card', [
                'title' => 'Onay Orani',
                'body' => 'Son 30 gunde onay orani %63 seviyesinde.',
            ]),
            'quickForm' => $this->render('admin/components/form'),
            'latestTable' => $this->render('admin/components/table'),
            'saveButton' => $this->render('admin/components/button', [
                'label' => 'Yeni Teklif',
                'variant' => 'primary',
            ]),
            'filterButton' => $this->render('admin/components/button', [
                'label' => 'Filtre',
                'variant' => 'ghost',
            ]),
        ]);

        $html = $this->render('admin/layout', [
            'title' => 'Kirpi Admin Demo',
            'heroTitle' => 'Kirpi Admin Demo',
            'heroSubtitle' => 'Teklif, recete ve CMS benzeri uygulamalar icin sade panel taslagi.',
            'content' => $content,
        ]);

        return Response::make($html, 200, ['Content-Type' => 'text/html; charset=utf-8']);
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
