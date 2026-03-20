<?php

declare(strict_types=1);

namespace Core\Model\Relations;

use Core\Database\Result\Collection;

class BelongsToMany extends Relation
{
    public function __construct(
        string $related,
        object $parent,
        protected string $pivotTable,
        string $foreignKey,
        string $relatedKey,
    ) {
        parent::__construct($related, $parent, $foreignKey, $relatedKey);
    }

    public function getResults(): Collection
    {
        $relatedTable = (new $this->related)->getTable();

        return $this->relatedQuery()
            ->join(
                $this->pivotTable,
                "{$this->pivotTable}.{$this->localKey}",
                '=',
                "{$relatedTable}.id"
            )
            ->where("{$this->pivotTable}.{$this->foreignKey}", $this->parent->{$this->parent->primaryKey})
            ->get();
    }

    public function attach(int|array $ids, array $pivot = []): void
    {
        foreach ((array) $ids as $id) {
            app(\Core\Database\DatabaseManager::class)
                ->table($this->pivotTable)
                ->updateOrInsert([
                    $this->foreignKey => $this->parent->getKey(),
                    $this->localKey   => $id,
                ], $pivot);
        }
    }

    public function detach(int|array|null $ids = null): void
    {
        $query = app(\Core\Database\DatabaseManager::class)
            ->table($this->pivotTable)
            ->where($this->foreignKey, $this->parent->getKey());

        if ($ids !== null) {
            $query->whereIn($this->localKey, (array) $ids);
        }

        $query->delete();
    }

    public function sync(array $ids): void
    {
        $this->detach();
        $this->attach($ids);
    }

    public function toggle(int|array $ids): void
    {
        $current = app(\Core\Database\DatabaseManager::class)
            ->table($this->pivotTable)
            ->where($this->foreignKey, $this->parent->getKey())
            ->pluck($this->localKey);

        $attach = array_diff((array) $ids, $current);
        $detach = array_intersect((array) $ids, $current);

        $this->attach($attach);
        $this->detach($detach);
    }
}