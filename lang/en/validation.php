<?php

declare(strict_types=1);

return [
    'required'    => 'The :attribute field is required.',
    'email'       => 'The :attribute must be a valid email address.',
    'min'         => [
        'string'  => 'The :attribute must be at least :min characters.',
        'numeric' => 'The :attribute must be at least :min.',
        'array'   => 'The :attribute must have at least :min items.',
    ],
    'max'         => [
        'string'  => 'The :attribute may not be greater than :max characters.',
        'numeric' => 'The :attribute may not be greater than :max.',
        'array'   => 'The :attribute may not have more than :max items.',
    ],
    'unique'      => 'The :attribute has already been taken.',
    'confirmed'   => 'The :attribute confirmation does not match.',
    'string'      => 'The :attribute must be a string.',
    'integer'     => 'The :attribute must be an integer.',
    'numeric'     => 'The :attribute must be numeric.',
    'boolean'     => 'The :attribute must be true or false.',
    'in'          => 'The selected :attribute is invalid.',
    'not_in'      => 'The selected :attribute is invalid.',
    'exists'      => 'The selected :attribute is invalid.',
    'date'        => 'The :attribute is not a valid date.',
    'url'         => 'The :attribute must be a valid URL.',
    'alpha'       => 'The :attribute may only contain letters.',
    'alpha_num'   => 'The :attribute may only contain letters and numbers.',
    'alpha_dash'  => 'The :attribute may only contain letters, numbers, dashes and underscores.',
    'between'     => 'The :attribute must be between :min and :max.',
    'regex'       => 'The :attribute format is invalid.',
];