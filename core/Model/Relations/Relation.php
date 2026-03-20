<?php

declare(strict_types=1);

namespace Core\Model\Relations;

abstract class Relation
{
    public function __construct(
        protected string $related,
        protected object $parent,
        protected string $foreignKey,
        protected string $localKey,
    ) {}

    abstract public function getResults(): mixed;

    protected function relatedQuery(): \Core\Database\QueryBuilder
    {
        return (new $this->related)->query();
    }
}