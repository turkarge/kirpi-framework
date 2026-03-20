<?php

declare(strict_types=1);

namespace Core\Model\Concerns;

trait HasCasts
{
    protected function castGet(string $key, mixed $value): mixed
    {
        if ($value === null) return null;

        return match($this->casts[$key]) {
            'int', 'integer'   => (int) $value,
            'float', 'double'  => (float) $value,
            'string'           => (string) $value,
            'bool', 'boolean'  => (bool) $value,
            'array'            => is_string($value) ? json_decode($value, true) : (array) $value,
            'json'             => is_string($value) ? json_decode($value, true) : $value,
            'object'           => is_string($value) ? json_decode($value) : (object) $value,
            'datetime'         => new \DateTime($value),
            'date'             => (new \DateTime($value))->setTime(0, 0),
            'timestamp'        => strtotime($value),
            'encrypted'        => $this->decrypt($value),
            default            => $value,
        };
    }

    protected function castSet(string $key, mixed $value): mixed
    {
        if ($value === null) return null;

        return match($this->casts[$key]) {
            'array', 'json', 'object' => json_encode($value),
            'datetime', 'date'        => $value instanceof \DateTime
                ? $value->format('Y-m-d H:i:s')
                : $value,
            'encrypted'               => $this->encrypt($value),
            default                   => $value,
        };
    }

    private function encrypt(string $value): string
    {
        $key = env('APP_KEY', '');
        $iv  = substr(md5($key), 0, 16);

        return base64_encode(
            openssl_encrypt($value, 'AES-256-CBC', $key, 0, $iv)
        );
    }

    private function decrypt(string $value): string
    {
        $key = env('APP_KEY', '');
        $iv  = substr(md5($key), 0, 16);

        return openssl_decrypt(
            base64_decode($value),
            'AES-256-CBC',
            $key,
            0,
            $iv
        );
    }
}