<?php

declare(strict_types=1);

namespace Core\Model\Relations;

use Core\Database\Result\Collection;

class HasMany extends Relation
{
    public function getResults(): Collection
    {
        return $this->relatedQuery()
            ->where($this->foreignKey, $this->parent->{$this->localKey})
            ->get();
    }

    public function create(array $attributes): object
    {
        $attributes[$this->foreignKey] = $this->parent->{$this->localKey};
        return ($this->related)::create($attributes);
    }

    public function where(string $column, mixed $value): \Core\Database\QueryBuilder
    {
        return $this->relatedQuery()
            ->where($this->foreignKey, $this->parent->{$this->localKey})
            ->where($column, $value);
    }
}