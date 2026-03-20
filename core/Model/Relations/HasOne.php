<?php

declare(strict_types=1);

namespace Core\Model\Relations;

class HasOne extends Relation
{
    public function getResults(): ?object
    {
        return $this->relatedQuery()
            ->where($this->foreignKey, $this->parent->{$this->localKey})
            ->first();
    }

    public function create(array $attributes): object
    {
        $attributes[$this->foreignKey] = $this->parent->{$this->localKey};
        return ($this->related)::create($attributes);
    }
}