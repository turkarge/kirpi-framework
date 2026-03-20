<?php

declare(strict_types=1);

namespace Core\Model\Concerns;

use Core\Model\Relations\HasOne;
use Core\Model\Relations\HasMany;
use Core\Model\Relations\BelongsTo;
use Core\Model\Relations\BelongsToMany;

trait HasRelationships
{
    protected function hasOne(string $related, ?string $foreignKey = null, ?string $localKey = null): HasOne
    {
        $foreignKey ??= strtolower(class_basename(static::class)) . '_id';
        $localKey   ??= $this->primaryKey;

        return new HasOne($related, $this, $foreignKey, $localKey);
    }

    protected function hasMany(string $related, ?string $foreignKey = null, ?string $localKey = null): HasMany
    {
        $foreignKey ??= strtolower(class_basename(static::class)) . '_id';
        $localKey   ??= $this->primaryKey;

        return new HasMany($related, $this, $foreignKey, $localKey);
    }

    protected function belongsTo(string $related, ?string $foreignKey = null, ?string $ownerKey = null): BelongsTo
    {
        $foreignKey ??= strtolower(class_basename($related)) . '_id';
        $ownerKey   ??= (new $related)->primaryKey;

        return new BelongsTo($related, $this, $foreignKey, $ownerKey);
    }

    protected function belongsToMany(
        string  $related,
        ?string $pivotTable = null,
        ?string $foreignKey = null,
        ?string $relatedKey = null,
    ): BelongsToMany {
        $pivotTable ??= $this->guessPivotTable($related);
        $foreignKey ??= strtolower(class_basename(static::class)) . '_id';
        $relatedKey ??= strtolower(class_basename($related)) . '_id';

        return new BelongsToMany($related, $this, $pivotTable, $foreignKey, $relatedKey);
    }

    public static function with(string ...$relations): \Core\Database\QueryBuilder
    {
        return static::query()->with(...$relations);
    }

    private function guessPivotTable(string $related): string
    {
        $tables = [
            strtolower(class_basename(static::class)),
            strtolower(class_basename($related)),
        ];
        sort($tables);
        return implode('_', $tables);
    }
}