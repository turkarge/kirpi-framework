<?php

declare(strict_types=1);

return [
    'welcome' => [
        'subject' => "Kirpi Framework'e Hos Geldiniz!",
        'greeting' => 'Merhaba :name,',
        'body' => "Kirpi Framework'e hos geldiniz! Sisteme basariyla kayit oldunuz.",
        'footer' => 'Bu e-posta Kirpi Framework tarafindan gonderilmistir.',
    ],
    'password_reset' => [
        'subject' => ':app sifre sifirlama',
        'greeting' => 'Merhaba,',
        'intro' => ':app hesabin icin sifre sifirlama talebi aldik.',
        'action' => 'Sifreyi sifirla',
        'expiry' => 'Bu baglanti :minutes dakika boyunca gecerlidir.',
        'ignore' => 'Bu islemi sen yapmadiysan bu e-postayi dikkate alma.',
    ],
];

