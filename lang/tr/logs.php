<?php

declare(strict_types=1);

return [
    'meta_title' => 'Log Goruntuleyici',
    'pretitle' => 'Izleme',
    'title' => 'Sistem Loglari',
    'subtitle' => 'Kanal bazli log dosyalarini guvenli sekilde goruntule.',
    'form' => [
        'file' => 'Log Dosyasi',
        'lines' => 'Satir Limiti',
        'search' => 'Ara',
        'channel' => 'Kanal',
        'level' => 'Seviye',
        'all' => 'Tum',
    ],
    'actions' => [
        'refresh' => 'Yenile',
        'download' => 'Tamamini Ac',
    ],
    'stats' => [
        'title' => 'Ozet',
        'rows' => 'Gorunen Satir',
        'parsed_rows' => 'Ayrisabilen Kayit',
        'size' => 'Dosya Boyutu',
        'updated_at' => 'Son Guncelleme',
    ],
    'output' => [
        'title' => 'Log Ciktisi',
        'table_title' => 'Tablo',
        'raw_title' => 'Ham',
        'filtered_rows' => 'Filtrelenen Satir',
    ],
    'table' => [
        'empty' => 'Heniz log dosyasi olusmamis.',
        'empty_rows' => 'Secilen filtrede kayit bulunamadi.',
        'time' => 'Zaman',
        'channel' => 'Kanal',
        'level' => 'Seviye',
        'message' => 'Mesaj',
        'request_id' => 'Request ID',
        'path' => 'Yol',
        'status' => 'Status',
        'duration_ms' => 'Sure (ms)',
        'user_id' => 'Kullanici',
    ],
];
