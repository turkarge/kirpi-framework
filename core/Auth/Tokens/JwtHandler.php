<?php

declare(strict_types=1);

namespace Core\Auth\Tokens;

class JwtHandler
{
    private string $algo = 'HS256';

    public function __construct(
        private readonly string $secret,
    ) {}

    public function encode(array $payload): string
    {
        $header  = $this->base64url(json_encode(['typ' => 'JWT', 'alg' => $this->algo]));
        $payload = $this->base64url(json_encode($payload));
        $sig     = $this->base64url($this->sign("{$header}.{$payload}"));

        return "{$header}.{$payload}.{$sig}";
    }

    public function decode(string $token): ?array
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) return null;

        [$header, $payload, $sig] = $parts;

        // İmza doğrula
        $expected = $this->base64url($this->sign("{$header}.{$payload}"));

        if (!hash_equals($expected, $sig)) return null;

        $data = json_decode(
            base64_decode(strtr($payload, '-_', '+/')),
            true
        );

        if (!is_array($data)) return null;

        // Süre kontrolü
        if (isset($data['exp']) && $data['exp'] < time()) return null;

        // Blacklist kontrolü
        if (isset($data['jti']) && $this->isBlacklisted($data['jti'])) return null;

        return $data;
    }

    public function blacklist(string $jti, int $ttl = 3600): void
    {
        // Redis varsa kullan, yoksa session'a yaz
        if (isset($_SESSION)) {
            $_SESSION['jwt_blacklist'][$jti] = time() + $ttl;
        }
    }

    public function isBlacklisted(string $jti): bool
    {
        if (!isset($_SESSION['jwt_blacklist'][$jti])) {
            return false;
        }

        // Süresi dolmuşsa temizle
        if ($_SESSION['jwt_blacklist'][$jti] < time()) {
            unset($_SESSION['jwt_blacklist'][$jti]);
            return false;
        }

        return true;
    }

    private function sign(string $data): string
    {
        return hash_hmac('sha256', $data, $this->secret, true);
    }

    private function base64url(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}