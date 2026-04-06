<?php

declare(strict_types=1);

return [
    'failed'   => 'Bu kimlik bilgileri kayıtlarımızla eşleşmiyor.',
    'throttle' => 'Çok fazla giriş denemesi. ütfen :seconds saniye sonra tekrar deneyin.',
    'logout'   => 'Başarıyla çıkış yapıldı.',
    'login'    => 'Başarıyla giriş yapıldı.',
    'web' => [
        'common' => [
            'or' => 'veya',
            'back_to_login' => 'Giriş ekranına dön',
            'terms' => 'Kullanım Şartları',
            'logout' => 'Çıkış',
        ],
        'fields' => [
            'email' => 'E-posta',
            'password' => 'Şifre',
            'user' => 'Kullanıcı',
            'guard' => 'Guard',
        ],
        'login' => [
            'meta_title' => 'Kirpi Login',
            'title' => 'Hesabina Giriş Yap',
            'forgot' => 'Şifremi unuttum',
            'remember' => 'Beni hatırla',
            'submit' => 'Giriş Yap',
            'error_required' => 'E-posta ve şifre zorunludur.',
            'error_invalid' => 'Giriş bilgileri geçersiz.',
        ],
        'forgot' => [
            'meta_title' => 'Şifre Hatırlat',
            'title' => 'Şifreni mi unuttun?',
            'description' => 'E-posta adresini gir. Şifre sıfırlama bağlantısını e-posta ile gönderelim.',
            'submit' => 'Sıfırlama Linki Gonder',
            'accept_prefix' => 'Devam ederek',
            'accept_suffix' => 'kabul etmiş olursun.',
            'error_invalid_email' => 'Geçerli bir e-posta gir.',
            'success' => 'Şifre sıfırlama bağlantısı hazırlandı (simulasyon).',
        ],
        'tos' => [
            'meta_title' => 'Kullanım Şartları',
            'title' => 'Kullanım Şartları',
            'p1' => ':app kisisel ve kucuk/orta olcekli uygulamalar icin tasarlanmis bir cekirdektir.',
            'p2' => 'Bu ornek sayfa bir yasal taslak yerine UI ve akisi dogrulamak icin tutulur.',
            'p3' => 'Uretim ortami icin uygulamana ozel gizlilik politikasi ve kullanim sartlarini eklemen gerekir.',
        ],
        'lock' => [
            'meta_title' => 'Kilidi Ac',
            'description' => 'Oturum kilitlendi. Devam etmek icin sifreni gir.',
            'submit' => 'Kilidi Ac',
            'switch_account' => 'Farkli hesapla giris yap',
            'error_required' => 'Sifre zorunludur.',
            'error_invalid' => 'Hatali sifre.',
        ],
        'dashboard' => [
            'meta_title' => 'Kirpi Core Dashboard',
            'title' => 'Core Dashboard',
            'subtitle' => 'Kimlik dogrulama sonrasi varsayilan kontrol noktasi.',
            'welcome' => 'Hos Geldin, :name',
            'description' => ':app cekirdegi hazir. Bundan sonraki adimda uygulamana ozel modulleri make:module ve make:crud komutlariyla ekleyebilirsin.',
            'landing' => 'Landing',
            'account_summary' => 'Hesap Ozeti',
        ],
    ],
];

