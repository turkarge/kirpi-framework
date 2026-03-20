<?php

declare(strict_types=1);

namespace Core\Model\Concerns;

trait HasScopes
{
    protected static array $globalScopes = [];

    public static function addGlobalScope(string $name, \Closure $scope): void
    {
        static::$globalScopes[static::class][$name] = $scope;
    }

    public static function removeGlobalScope(string $name): void
    {
        unset(static::$globalScopes[static::class][$name]);
    }

    public static function withoutGlobalScope(string $name): \Core\Database\QueryBuilder
    {
        $query = static::query();
        unset(static::$globalScopes[static::class][$name]);
        return $query;
    }

    public static function withTrashed(): \Core\Database\QueryBuilder
    {
        return static::withoutGlobalScope('softDelete');
    }

    public static function onlyTrashed(): \Core\Database\QueryBuilder
    {
        return static::withoutGlobalScope('softDelete')
            ->whereNotNull('deleted_at');
    }

    protected static function applyGlobalScopes(\Core\Database\QueryBuilder $query): \Core\Database\QueryBuilder
    {
        foreach (static::$globalScopes[static::class] ?? [] as $scope) {
            $scope($query);
        }

        return $query;
    }
}