<?php

declare(strict_types=1);

namespace Core\Migration;

class ColumnDefinition
{
    private array $attributes = [];

    public function __construct(
        private readonly string $type,
        private readonly string $name,
        array $params = [],
    ) {
        $this->attributes = $params;
    }

    public function nullable(bool $value = true): static
    {
        $this->attributes['nullable'] = $value;
        return $this;
    }

    public function default(mixed $value): static
    {
        $this->attributes['default'] = $value;
        return $this;
    }

    public function unsigned(): static
    {
        $this->attributes['unsigned'] = true;
        return $this;
    }

    public function primary(): static
    {
        $this->attributes['primary'] = true;
        return $this;
    }

    public function unique(): static
    {
        $this->attributes['unique'] = true;
        return $this;
    }

    public function index(): static
    {
        $this->attributes['index'] = true;
        return $this;
    }

    public function autoIncrement(): static
    {
        $this->attributes['autoIncrement'] = true;
        return $this;
    }

    public function after(string $column): static
    {
        $this->attributes['after'] = $column;
        return $this;
    }

    public function comment(string $text): static
    {
        $this->attributes['comment'] = $text;
        return $this;
    }

    public function charset(string $charset): static
    {
        $this->attributes['charset'] = $charset;
        return $this;
    }

    public function getType(): string  { return $this->type; }
    public function getName(): string  { return $this->name; }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->attributes[$key] ?? $default;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }
}