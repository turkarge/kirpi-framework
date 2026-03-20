<?php

declare(strict_types=1);

namespace Core\Auth\Facades;

use Core\Auth\AuthManager;
use Core\Auth\Contracts\AuthenticatableInterface;
use Core\Auth\Contracts\GuardInterface;

class Auth
{
    public static function guard(?string $name = null): GuardInterface
    {
        return app(AuthManager::class)->guard($name);
    }

    public static function check(): bool
    {
        return app(AuthManager::class)->check();
    }

    public static function guest(): bool
    {
        return app(AuthManager::class)->guest();
    }

    public static function user(): ?AuthenticatableInterface
    {
        return app(AuthManager::class)->user();
    }

    public static function id(): int|string|null
    {
        return app(AuthManager::class)->id();
    }

    public static function attempt(array $credentials, bool $remember = false): bool
    {
        return app(AuthManager::class)->attempt($credentials, $remember);
    }

    public static function login(AuthenticatableInterface $user, bool $remember = false): void
    {
        app(AuthManager::class)->login($user, $remember);
    }

    public static function logout(): void
    {
        app(AuthManager::class)->logout();
    }
}