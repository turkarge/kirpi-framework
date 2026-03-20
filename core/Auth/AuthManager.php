<?php

declare(strict_types=1);

namespace Core\Auth;

use Core\Auth\Contracts\GuardInterface;
use Core\Auth\Contracts\AuthenticatableInterface;
use Core\Auth\Guards\SessionGuard;
use Core\Auth\Guards\JwtGuard;
use Core\Auth\Providers\DatabaseProvider;
use Core\Auth\Tokens\JwtHandler;
use Core\Database\DatabaseManager;

class AuthManager
{
    private array $guards    = [];
    private array $resolved  = [];

    public function __construct(
        private readonly array           $config,
        private readonly DatabaseManager $db,
    ) {}

    // ─── Guard Çözümleme ─────────────────────────────────────

    public function guard(?string $name = null): GuardInterface
    {
        $name ??= $this->config['default'] ?? 'session';

        if (isset($this->resolved[$name])) {
            return $this->resolved[$name];
        }

        $config = $this->config['guards'][$name]
            ?? throw new \InvalidArgumentException("Auth guard [{$name}] not configured.");

        return $this->resolved[$name] = $this->createGuard($name, $config);
    }

    private function createGuard(string $name, array $config): GuardInterface
    {
        $provider = $this->createProvider($config['provider'] ?? 'users');

        return match($config['driver']) {
            'session' => new SessionGuard($provider, $this->config),
            'jwt'     => new JwtGuard(
                $provider,
                new JwtHandler(env('JWT_SECRET', 'kirpi-secret')),
                $config
            ),
            default => throw new \RuntimeException("Auth driver [{$config['driver']}] not supported.")
        };
    }

    private function createProvider(string $name): \Core\Auth\Contracts\ProviderInterface
    {
        $config = $this->config['providers'][$name]
            ?? throw new \InvalidArgumentException("Auth provider [{$name}] not configured.");

        return match($config['driver']) {
            'database' => new DatabaseProvider($this->db, $config),
            default    => throw new \RuntimeException("Auth provider driver [{$config['driver']}] not supported.")
        };
    }

    // ─── Proxy Methods ───────────────────────────────────────

    public function check(): bool
    {
        return $this->guard()->check();
    }

    public function guest(): bool
    {
        return $this->guard()->guest();
    }

    public function user(): ?AuthenticatableInterface
    {
        return $this->guard()->user();
    }

    public function id(): int|string|null
    {
        return $this->guard()->id();
    }

    public function attempt(array $credentials, bool $remember = false): bool
    {
        return $this->guard()->attempt($credentials, $remember);
    }

    public function login(AuthenticatableInterface $user, bool $remember = false): void
    {
        $this->guard()->login($user, $remember);
    }

    public function logout(): void
    {
        $this->guard()->logout();
    }
}