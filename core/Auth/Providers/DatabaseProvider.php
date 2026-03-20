<?php

declare(strict_types=1);

namespace Core\Auth\Providers;

use Core\Auth\Contracts\AuthenticatableInterface;
use Core\Auth\Contracts\ProviderInterface;
use Core\Database\DatabaseManager;

class DatabaseProvider implements ProviderInterface
{
    public function __construct(
        private readonly DatabaseManager $db,
        private readonly array           $config,
    ) {}

    public function findById(int|string $id): ?AuthenticatableInterface
    {
        $table = $this->config['table'] ?? 'users';
        $model = $this->config['model'] ?? null;

        $result = $this->db->table($table)->find($id);

        if ($result === null) return null;

        if ($model && class_exists($model)) {
            return (new $model)->newFromDatabase((array) $result);
        }

        return $result;
    }

    public function findByCredentials(array $credentials): ?AuthenticatableInterface
    {
        $table    = $this->config['table'] ?? 'users';
        $model    = $this->config['model'] ?? null;
        $password = $credentials['password'] ?? null;

        $query = $this->db->table($table);

        foreach ($credentials as $key => $value) {
            if ($key === 'password') continue;
            $query->where($key, $value);
        }

        $result = $query->first();

        if ($result === null) return null;

        if ($model && class_exists($model)) {
            return (new $model)->newFromDatabase((array) $result);
        }

        return $result;
    }

    public function validateCredentials(AuthenticatableInterface $user, array $credentials): bool
    {
        $password = $credentials['password'] ?? '';
        return password_verify($password, $user->getAuthPassword());
    }
}