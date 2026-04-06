<?php

declare(strict_types=1);

return [
    'meta_title' => 'Kullanici Yonetimi',
    'pretitle' => 'Yonetim',
    'title' => 'Kullanici Yonetimi',
    'subtitle' => 'Kullanicilari listele, durumlarini izle ve yonetim akislarini tek ekrandan kontrol et.',
    'actions' => [
        'new' => 'Yeni Kullanici',
        'edit' => 'Duzenle',
        'detail' => 'Detay',
        'create' => 'Olustur',
        'cancel' => 'Vazgec',
        'save' => 'Kaydet',
        'back_to_list' => 'Kullanici Listesine Don',
    ],
    'status' => [
        'active' => 'Aktif',
        'passive' => 'Pasif',
    ],
    'filters' => [
        'search' => 'Kullanici ara...',
        'all' => 'Tum Durumlar',
    ],
    'table' => [
        'title' => 'Kullanici Listesi',
        'name' => 'Ad Soyad',
        'email' => 'E-Posta',
        'last_login_at' => 'Son Giris',
        'updated_at' => 'Guncellenme',
        'status' => 'Durum',
        'empty' => 'Filtreye uygun kullanici kaydi bulunamadi.',
    ],
    'form' => [
        'name' => 'Ad Soyad',
        'email' => 'E-Posta',
        'password' => 'Sifre',
        'locale' => 'Dil',
        'status' => 'Durum',
    ],
    'modal' => [
        'new_title' => 'Yeni Kullanici Olustur',
    ],
    'detail' => [
        'meta_title' => 'Kullanici Detayi: :name',
        'title' => 'Kullanici Inceleme',
        'subtitle' => ':name kullanicisinin okunur ozeti',
        'info_title' => 'Hesap Bilgileri',
    ],
    'edit' => [
        'meta_title' => 'Kullanici Duzenle: :name',
        'title' => 'Kullanici Duzenleme',
    ],
    'flash' => [
        'success_title' => 'Basarili',
        'warning_title' => 'Uyari',
        'error_title' => 'Hata',
        'updated' => 'Kullanici kaydi guncellendi.',
        'created' => 'Kullanici kaydi olusturuldu.',
        'validation_failed' => 'Lutfen ad, e-posta ve durum alanlarini kontrol edin.',
        'create_validation_failed' => 'Lutfen ad, e-posta ve sifre alanlarini kontrol edin. (Sifre min: 6)',
        'email_taken' => 'Bu e-posta adresi baska bir kullanici tarafindan kullaniliyor.',
        'not_found' => 'Kullanici kaydi bulunamadi.',
    ],
    'side' => [
        'title' => 'Kullanim Notu',
        'description' => 'Kullanici listesi su an aktif/pasif filtre ve arama ile sade sekilde yonetilir.',
        'hint' => 'Kullanici duzenleme ve detay sayfalari bir sonraki iterasyonda tamamlanacak.',
    ],
    'footer' => [
        'dashboard' => 'Dashboard',
        'terms' => 'Kullanim Sartlari',
    ],
];
