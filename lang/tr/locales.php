<?php

declare(strict_types=1);

return [
    'meta_title' => 'Dil Yonetimi',
    'pretitle' => 'Yonetim',
    'title' => 'Dil Yonetimi',
    'subtitle' => 'Varsayilan dili ve aktif dil listesini tek noktadan yonet.',
    'actions' => [
        'save' => 'Kaydet',
        'edit_locale' => 'Dili Duzenle',
        'save_translations' => 'Cevirileri Kaydet',
    ],
    'card' => [
        'title' => 'Dil Ayarlari',
        'description' => 'Bu ayarlar .env dosyasina yazilir ve uygulama genelinde kullanilir.',
    ],
    'form' => [
        'default_locale' => 'Varsayilan Dil',
        'enabled_locales' => 'Aktif Diller',
        'edit_locale' => 'Duzenlenecek Dil',
        'group' => 'Dosya (Grup)',
        'filter' => 'Anahtar veya metin ile filtrele...',
    ],
    'translations' => [
        'title' => 'Hizli Ceviri',
        'description' => 'Duzenlenecek dili ve dosyayi sec, metinleri satir satir hizlica guncelle.',
    ],
    'table' => [
        'key' => 'Anahtar',
        'target' => 'Metin',
        'empty' => 'Secilen dosyada ceviri anahtari bulunamadi.',
    ],
    'current' => [
        'title' => 'Mevcut Konfigurasyon',
        'default_locale' => 'Varsayilan',
        'enabled_locales' => 'Aktif Diller',
    ],
    'flash' => [
        'success_title' => 'Basarili',
        'warning_title' => 'Uyari',
        'updated' => 'Dil ayarlari guncellendi.',
        'translations_updated' => 'Ceviri dosyasi guncellendi.',
        'invalid_default' => 'Secilen varsayilan dil desteklenmiyor.',
        'invalid_locale' => 'Secilen dil gecersiz.',
        'invalid_group' => 'Secilen dil dosyasi gecersiz.',
        'empty_enabled' => 'En az bir aktif dil secilmelidir.',
    ],
    'footer' => [
        'dashboard' => 'Dashboard',
        'terms' => 'Kullanim Sartlari',
    ],
];
