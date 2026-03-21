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

    /** @param array<string, mixed> $data */
    private function render(string $view, array $data = []): string
    {
        extract($data, EXTR_SKIP);

        ob_start();
        require __DIR__ . '/templates/' . $view . '.php';

        return (string) ob_get_clean();
    }
}
