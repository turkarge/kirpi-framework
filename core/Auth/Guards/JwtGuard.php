<?php

declare(strict_types=1);

namespace Core\Auth\Guards;

use Core\Auth\Contracts\AuthenticatableInterface;
use Core\Auth\Contracts\GuardInterface;
use Core\Auth\Contracts\ProviderInterface;
use Core\Auth\Tokens\JwtHandler;

class JwtGuard implements GuardInterface
{
    private ?AuthenticatableInterface $currentUser = null;
    private ?array $payload = null;
    private ?string $currentToken = null;

    public function __construct(
        private readonly ProviderInterface $provider,
        private readonly JwtHandler        $jwt,
        private readonly array             $config,
    ) {}

    public function check(): bool
    {
        return $this->user() !== null;
    }

    public function guest(): bool
    {
        return !$this->check();
    }

    public function user(): ?AuthenticatableInterface
    {
        if ($this->currentUser !== null && $this->currentToken === null) {
            return $this->currentUser;
        }

        $token = $this->extractToken();

        if ($token === null) {
            $this->currentUser = null;
            $this->payload = null;
            $this->currentToken = null;
            return null;
        }

        if ($this->currentUser !== null && $this->currentToken === $token) {
            return $this->currentUser;
        }

        $payload = $this->jwt->decode($token);

        if ($payload === null) {
            $this->currentUser = null;
            $this->payload = null;
            $this->currentToken = null;
            return null;
        }

        $this->currentToken = $token;
        $this->payload     = $payload;
        $this->currentUser = $this->provider->findById($payload['sub']);

        return $this->currentUser;
    }

    public function id(): int|string|null
    {
        return $this->user()?->getAuthId();
    }

    public function attempt(array $credentials, bool $remember = false): bool
    {
        $user = $this->provider->findByCredentials($credentials);

        if ($user === null || !$this->provider->validateCredentials($user, $credentials)) {
            return false;
        }

        $this->login($user);
        return true;
    }

    public function login(AuthenticatableInterface $user, bool $remember = false): void
    {
        $this->currentUser = $user;
    }

    public function logout(): void
    {
        if ($this->payload !== null && isset($this->payload['jti'])) {
            $ttl = $this->config['ttl'] ?? 3600;
            $this->jwt->blacklist($this->payload['jti'], $ttl);
        }

        $this->currentUser = null;
        $this->payload     = null;
        $this->currentToken = null;
    }

    // ─── Token İşlemleri ─────────────────────────────────────

    public function issueTokens(AuthenticatableInterface $user): array
    {
        $ttl     = $this->config['ttl']     ?? 3600;
        $refresh = $this->config['refresh'] ?? 604800;

        return [
            'access_token'  => $this->jwt->encode([
                'sub'  => $user->getAuthId(),
                'type' => 'access',
                'jti'  => bin2hex(random_bytes(16)),
                'iat'  => time(),
                'exp'  => time() + $ttl,
            ]),
            'refresh_token' => $this->jwt->encode([
                'sub'  => $user->getAuthId(),
                'type' => 'refresh',
                'jti'  => bin2hex(random_bytes(16)),
                'iat'  => time(),
                'exp'  => time() + $refresh,
            ]),
            'expires_in'    => $ttl,
            'token_type'    => 'Bearer',
        ];
    }

    public function refresh(string $refreshToken): array
    {
        $payload = $this->jwt->decode($refreshToken);

        if ($payload === null || ($payload['type'] ?? '') !== 'refresh') {
            throw new \RuntimeException('Invalid refresh token.');
        }

        $user = $this->provider->findById($payload['sub']);

        if ($user === null) {
            throw new \RuntimeException('User not found.');
        }

        // Eski refresh token'ı geçersiz kıl
        $this->jwt->blacklist($payload['jti'], $this->config['refresh'] ?? 604800);

        return $this->issueTokens($user);
    }

    public function getPayload(): ?array
    {
        return $this->payload;
    }

    // ─── Token Çıkarma ───────────────────────────────────────

    private function extractToken(): ?string
    {
        $request = null;

        if (function_exists('app')) {
            try {
                $request = app(\Core\Http\Request::class);
            } catch (\Throwable) {
                $request = null;
            }
        }

        if ($request instanceof \Core\Http\Request) {
            $header = $request->header('Authorization', '');
            if (is_string($header) && str_starts_with($header, 'Bearer ')) {
                return substr($header, 7);
            }

            $queryToken = $request->get('token');
            if (is_string($queryToken) && $queryToken !== '') {
                return $queryToken;
            }
        }

        // Authorization: Bearer {token}
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

        if (str_starts_with($header, 'Bearer ')) {
            return substr($header, 7);
        }

        // Query string: ?token=xxx
        return $_GET['token'] ?? null;
    }
}
