<?php

declare(strict_types=1);

return [
    'meta_title' => 'Rol Yonetimi',
    'pretitle' => 'Yetkilendirme',
    'title' => 'Rol Yonetimi',
    'subtitle' => 'Sistemdeki rollerin erisim kapsamlarini sade ve kontrollu bicimde yonet.',
    'actions' => [
        'new' => 'Yeni Rol',
        'matrix' => 'Yetki Matrisi',
        'view' => 'Incele',
        'edit' => 'Duzenle',
        'clone' => 'Kopyala',
        'save' => 'Kaydet',
        'delete' => 'Sil',
        'cancel' => 'Vazgec',
        'create' => 'Olustur',
        'back_to_list' => 'Role Listesine Don',
    ],
    'status' => [
        'active' => 'Aktif',
        'passive' => 'Pasif',
        'active_detail' => ':count kullaniciya atanmis',
        'passive_detail' => 'Erisim devre disi',
    ],
    'filters' => [
        'search' => 'Rol ara...',
        'all' => 'Tum Durumlar',
    ],
    'table' => [
        'title' => 'Rol Listesi',
        'role' => 'Rol',
        'user_count' => 'Kullanici',
        'status' => 'Durum',
        'updated_at' => 'Guncellenme',
        'actions' => 'Aksiyon',
        'empty' => 'Filtreye uygun rol kaydi bulunamadi.',
    ],
    'side' => [
        'title' => 'Rol Politikasi',
        'description' => 'Rol degisikliklerini yayinlamadan once izin setini ve etkilenen kullanicilari kontrol et.',
        'hint' => 'Kritik rollerde silme yerine pasife alma yaklasimini tercih et.',
    ],
    'modal' => [
        'new_title' => 'Yeni Rol Olustur',
    ],
    'form' => [
        'title' => 'Rol Detayi',
        'name' => 'Rol Adi',
        'slug' => 'Slug',
        'description' => 'Aciklama',
        'status' => 'Durum',
    ],
    'permissions' => [
        'title' => 'Yetkiler',
    ],
    'audit' => [
        'title' => 'Son Degisiklikler',
        'placeholder' => 'Degisiklik kayitlari burada listelenir.',
    ],
    'detail' => [
        'meta_title' => 'Rol Detayi: :role',
        'title' => 'Rol Inceleme',
        'subtitle' => ':role rolunun okunur ozeti',
    ],
    'edit' => [
        'meta_title' => 'Rol Duzenle: :role',
        'title' => 'Rol Duzenleme',
    ],
    'flash' => [
        'success_title' => 'Basarili',
        'warning_title' => 'Uyari',
        'error_title' => 'Hata',
        'created' => 'Rol kaydi olusturuldu.',
        'validation_failed' => 'Lutfen rol adi alanini kontrol edin.',
        'slug_taken' => 'Bu slug zaten kullaniliyor.',
        'status_updated' => 'Rol durumu guncellendi.',
        'not_found' => 'Rol kaydi bulunamadi.',
    ],
    'footer' => [
        'dashboard' => 'Dashboard',
        'terms' => 'Kullanim Sartlari',
    ],
];
