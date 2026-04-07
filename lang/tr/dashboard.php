<?php

declare(strict_types=1);

return [
    'meta_title' => 'Kirpi Core Dashboard',
    'title' => 'Core Dashboard',
    'subtitle' => 'Kimlik dogrulama sonrasi varsayilan kontrol noktasi.',
    'welcome' => 'Hos Geldin, :name',
    'description' => ':app cekirdegi hazir. Bundan sonraki adimda uygulamana ozel modulleri make:module ve make:crud komutlariyla ekleyebilirsin.',
    'account_summary' => 'Hesap Ozeti',
    'next_steps' => 'Sonraki Adimlar',
    'actions' => [
        'health' => 'Health',
        'ready' => 'Ready',
    ],
    'metrics' => [
        'routes' => 'Routes',
        'routes_note' => 'Temel sistem endpointleri aktif.',
        'modules' => 'Modules',
        'modules_note' => 'Yuklu modul klasorleri sayisi.',
        'database' => 'Database',
        'cache' => 'Cache',
    ],
    'fields' => [
        'users_total' => 'Kullanicilar',
        'roles_total' => 'Roller',
        'modules_total' => 'Moduller',
    ],
    'status' => [
        'up' => 'UP',
        'down' => 'DOWN',
        'na' => '-',
        'latency_up' => 'Saglikli ( :ms ms )',
        'latency_down' => 'Erisilemiyor ( :ms ms )',
    ],
    'table' => [
        'step_col' => 'Adim',
        'status_col' => 'Durum',
        'note_col' => 'Not',
        'ready' => 'Hazir',
        'pending' => 'Bekliyor',
        'step_module' => 'Uygulama modulu iskeletini olustur.',
        'step_crud' => 'Ilk yonetim CRUD akisini olustur.',
        'step_security' => 'Role/permission ve guvenlik baseline ayarlarini tamamla.',
        'detail_ok' => 'Kontrol tamam.',
        'detail_module_pending' => 'Mevcut modul sayisi: :count',
        'detail_crud_pending' => 'Kullanici: :users, Rol: :roles',
        'detail_security_pending' => 'DB: :db, Cache: :cache, Rol: :roles',
    ],
];
