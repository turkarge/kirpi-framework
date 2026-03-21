<?php

declare(strict_types=1);

namespace Core\Frontend\ViewModels;

final class AdminDemoViewModel
{
    /** @return array{title: string, heroTitle: string, heroSubtitle: string, cards: array<int, array{title: string, body: string}>, actions: array{save: string, filter: string}} */
    public function toArray(): array
    {
        return [
            'title' => 'Kirpi Admin Demo',
            'heroTitle' => 'Kirpi Admin Demo',
            'heroSubtitle' => 'Teklif, recete ve CMS benzeri uygulamalar icin sade panel taslagi.',
            'cards' => [
                [
                    'title' => 'Toplam Teklif',
                    'body' => 'Bu ay 42 adet teklif olusturuldu.',
                ],
                [
                    'title' => 'Onay Orani',
                    'body' => 'Son 30 gunde onay orani %63 seviyesinde.',
                ],
            ],
            'actions' => [
                'save' => 'Yeni Teklif',
                'filter' => 'Filtre',
            ],
        ];
    }
}
