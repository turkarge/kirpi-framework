<?php

declare(strict_types=1);

namespace Core\Auth\Contracts;

interface AuthenticatableInterface
{
    public function getAuthId(): int|string;
    public function getAuthPassword(): string;
    public function getAuthIdentifierName(): string;
    public function getRememberToken(): ?string;
    public function setRememberToken(string $token): void;
}