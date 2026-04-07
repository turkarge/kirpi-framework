<?php

declare(strict_types=1);

namespace Core\Auth\Guards;

use Core\Auth\Contracts\AuthenticatableInterface;
use Core\Auth\Contracts\GuardInterface;
use Core\Auth\Contracts\ProviderInterface;

class SessionGuard implements GuardInterface
{
    private ?AuthenticatableInterface $currentUser = null;

    public function __construct(
        private readonly ProviderInterface $provider,
        private readonly array             $config,
    ) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

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
        if ($this->currentUser !== null) {
            return $this->currentUser;
        }

        $id = $_SESSION['auth_id'] ?? null;

        if ($id === null) {
            return $this->checkRememberToken();
        }

        return $this->currentUser = $this->provider->findById($id);
    }

    public function id(): int|string|null
    {
        return $this->user()?->getAuthId();
    }

    public function attempt(array $credentials, bool $remember = false): bool
    {
        if ($this->isThrottled($credentials)) {
            throw new \RuntimeException('Too many login attempts. Please try again later.');
        }

        $user = $this->provider->findByCredentials($credentials);

        if ($user === null || !$this->provider->validateCredentials($user, $credentials)) {
            $this->incrementThrottle($credentials);
            return false;
        }

        $this->login($user, $remember);
        $this->clearThrottle($credentials);

        return true;
    }

    public function login(AuthenticatableInterface $user, bool $remember = false): void
    {
        session_regenerate_id(true);

        $_SESSION['auth_id']       = $user->getAuthId();
        $_SESSION['auth_guard']    = 'session';
        $_SESSION['auth_at']       = time();
        $_SESSION['last_activity'] = time();

        if ($remember) {
            $this->setRememberCookie($user);
        }

        $this->currentUser = $user;
    }

    public function logout(): void
    {
        $this->clearRememberCookie();

        unset(
            $_SESSION['auth_id'],
            $_SESSION['auth_guard'],
            $_SESSION['auth_at'],
            $_SESSION['last_activity'],
            $_SESSION['screen_locked'],
            $_SESSION['lock_return'],
            $_SESSION['pin_reset_verified_user_id'],
            $_SESSION['pin_reset_verified_at'],
            $_SESSION['password_reset_verified_user_id'],
            $_SESSION['password_reset_verified_at'],
        );

        session_regenerate_id(true);
        $this->currentUser = null;
    }

    public function validate(array $credentials): bool
    {
        $user = $this->provider->findByCredentials($credentials);

        if ($user === null) return false;

        return $this->provider->validateCredentials($user, $credentials);
    }

    // ─── Remember Me ─────────────────────────────────────────

    private function setRememberCookie(AuthenticatableInterface $user): void
    {
        $token = bin2hex(random_bytes(40));
        $user->setRememberToken($token);

        setcookie(
            name:     'remember_token',
            value:    $user->getAuthId() . '|' . $token,
            expires:  time() + (86400 * 30),
            path:     '/',
            secure:   true,
            httponly: true,
            samesite: 'Strict',
        );
    }

    private function clearRememberCookie(): void
    {
        setcookie('remember_token', '', time() - 3600, '/');
    }

    private function checkRememberToken(): ?AuthenticatableInterface
    {
        $cookie = $_COOKIE['remember_token'] ?? null;

        if ($cookie === null) return null;

        $parts = explode('|', $cookie, 2);

        if (count($parts) !== 2) return null;

        [$id, $token] = $parts;

        $user = $this->provider->findById($id);

        if ($user === null) return null;

        $storedToken = $user->getRememberToken();

        if ($storedToken === null || !hash_equals($storedToken, $token)) {
            return null;
        }

        $this->login($user);
        return $user;
    }

    // ─── Throttle ────────────────────────────────────────────

    private function isThrottled(array $credentials): bool
    {
        $key      = $this->throttleKey($credentials);
        $attempts = $_SESSION["throttle_{$key}_attempts"] ?? 0;
        $lastAt   = $_SESSION["throttle_{$key}_at"] ?? 0;
        $decay    = $this->config['throttle']['decay_seconds'] ?? 300;
        $max      = $this->config['throttle']['max_attempts']  ?? 5;

        if (time() - $lastAt > $decay) {
            $this->clearThrottle($credentials);
            return false;
        }

        return $attempts >= $max;
    }

    private function incrementThrottle(array $credentials): void
    {
        $key = $this->throttleKey($credentials);
        $_SESSION["throttle_{$key}_attempts"] = ($_SESSION["throttle_{$key}_attempts"] ?? 0) + 1;
        $_SESSION["throttle_{$key}_at"]       = time();
    }

    private function clearThrottle(array $credentials): void
    {
        $key = $this->throttleKey($credentials);
        unset(
            $_SESSION["throttle_{$key}_attempts"],
            $_SESSION["throttle_{$key}_at"]
        );
    }

    private function throttleKey(array $credentials): string
    {
        $identifier = $credentials['email'] ?? $credentials['username'] ?? '';
        $ip         = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        return hash('sha256', $identifier . $ip);
    }
}
