<?php

declare(strict_types=1);

namespace Core\Auth\Contracts;

interface ProviderInterface
{
    public function findById(int|string $id): ?AuthenticatableInterface;
    public function findByCredentials(array $credentials): ?AuthenticatableInterface;
    public function validateCredentials(AuthenticatableInterface $user, array $credentials): bool;
}