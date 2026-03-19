<?php

declare(strict_types=1);

namespace Core\Migration;

class ForeignKeyDefinition
{
    private string  $references = 'id';
    private string  $on         = '';
    private string  $onDelete   = 'RESTRICT';
    private string  $onUpdate   = 'RESTRICT';
    private ?string $keyName    = null;

    public function __construct(
        private readonly string $column,
        private readonly string $table,
    ) {}

    public function references(string $column): static
    {
        $this->references = $column;
        return $this;
    }

    public function on(string $table): static
    {
        $this->on = $table;
        return $this;
    }

    public function onDelete(string $action): static
    {
        $this->onDelete = strtoupper($action);
        return $this;
    }

    public function onUpdate(string $action): static
    {
        $this->onUpdate = strtoupper($action);
        return $this;
    }

    public function cascadeOnDelete(): static  { return $this->onDelete('CASCADE'); }
    public function nullOnDelete(): static     { return $this->onDelete('SET NULL'); }
    public function restrictOnDelete(): static { return $this->onDelete('RESTRICT'); }
    public function cascadeOnUpdate(): static  { return $this->onUpdate('CASCADE'); }

    public function name(string $name): static
    {
        $this->keyName = $name;
        return $this;
    }

    public function toArray(): array
    {
        return [
            'column'     => $this->column,
            'references' => $this->references,
            'on'         => $this->on,
            'onDelete'   => $this->onDelete,
            'onUpdate'   => $this->onUpdate,
            'name'       => $this->keyName ?? "{$this->table}_{$this->column}_foreign",
        ];
    }
}