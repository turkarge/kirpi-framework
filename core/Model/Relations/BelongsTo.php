<?php

declare(strict_types=1);

namespace Core\Model\Relations;

class BelongsTo extends Relation
{
    public function __construct(
        string $related,
        object $parent,
        string $foreignKey,
        protected string $ownerKey,
    ) {
        parent::__construct($related, $parent, $foreignKey, $ownerKey);
    }

    public function getResults(): ?object
    {
        return $this->relatedQuery()
            ->where($this->ownerKey, $this->parent->{$this->foreignKey})
            ->first();
    }

    public function associate(object $model): void
    {
        $this->parent->{$this->foreignKey} = $model->{$this->ownerKey};
    }

    public function dissociate(): void
    {
        $this->parent->{$this->foreignKey} = null;
    }
}