<?php

declare(strict_types=1);

return [
    'required'    => ':attribute alanı zorunludur.',
    'email'       => ':attribute geçerli bir e-posta adresi olmalıdır.',
    'min'         => [
        'string'  => ':attribute en az :min karakter olmalıdır.',
        'numeric' => ':attribute en az :min olmalıdır.',
        'array'   => ':attribute en az :min öğe içermelidir.',
    ],
    'max'         => [
        'string'  => ':attribute en fazla :max karakter olabilir.',
        'numeric' => ':attribute en fazla :max olabilir.',
        'array'   => ':attribute en fazla :max öğe içerebilir.',
    ],
    'unique'      => ':attribute zaten kullanılmaktadır.',
    'confirmed'   => ':attribute onayı eşleşmiyor.',
    'string'      => ':attribute bir metin olmalıdır.',
    'integer'     => ':attribute bir tam sayı olmalıdır.',
    'numeric'     => ':attribute sayısal bir değer olmalıdır.',
    'boolean'     => ':attribute doğru ya da yanlış olmalıdır.',
    'in'          => 'Seçilen :attribute geçersizdir.',
    'not_in'      => 'Seçilen :attribute geçersizdir.',
    'exists'      => 'Seçilen :attribute geçersizdir.',
    'date'        => ':attribute geçerli bir tarih olmalıdır.',
    'url'         => ':attribute geçerli bir URL olmalıdır.',
    'alpha'       => ':attribute yalnızca harf içerebilir.',
    'alpha_num'   => ':attribute yalnızca harf ve rakam içerebilir.',
    'alpha_dash'  => ':attribute yalnızca harf, rakam, tire ve alt çizgi içerebilir.',
    'between'     => ':attribute :min ile :max arasında olmalıdır.',
    'regex'       => ':attribute formatı geçersizdir.',
];