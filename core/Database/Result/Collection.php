<?php

declare(strict_types=1);

namespace Core\Database\Result;

class Collection implements \Countable, \IteratorAggregate, \ArrayAccess
{
    private array $items;

    public function __construct(array $items, private ?string $modelClass = null)
    {
        $this->items = array_map(function ($item) {
            if ($this->modelClass !== null && !($item instanceof $this->modelClass)) {
                $model = new $this->modelClass();
                return $model->newFromDatabase((array) $item);
            }
            return is_array($item) ? (object) $item : $item;
        }, $items);
    }

    public function first(): ?object         { return $this->items[0] ?? null; }
    public function last(): ?object          { return !empty($this->items) ? end($this->items) : null; }
    public function count(): int             { return count($this->items); }
    public function isEmpty(): bool          { return empty($this->items); }
    public function isNotEmpty(): bool       { return !$this->isEmpty(); }
    public function toArray(): array         { return $this->items; }

    public function map(\Closure $fn): static
    {
        return new static(array_map($fn, $this->items));
    }

    public function filter(\Closure $fn): static
    {
        return new static(array_values(array_filter($this->items, $fn)));
    }

    public function pluck(string $key): array
    {
        return array_map(
            fn($item) => is_object($item) ? ($item->$key ?? null) : ($item[$key] ?? null),
            $this->items
        );
    }

    public function keyBy(string $key): array
    {
        $result = [];
        foreach ($this->items as $item) {
            $k = is_object($item) ? ($item->$key ?? null) : ($item[$key] ?? null);
            if ($k !== null) $result[$k] = $item;
        }
        return $result;
    }

    public function groupBy(string $key): array
    {
        $groups = [];
        foreach ($this->items as $item) {
            $k = is_object($item) ? ($item->$key ?? null) : ($item[$key] ?? null);
            $groups[$k][] = $item;
        }
        return $groups;
    }

    public function each(\Closure $fn): static
    {
        foreach ($this->items as $key => $item) {
            $fn($item, $key);
        }
        return $this;
    }

    public function chunk(int $size): array
    {
        return array_chunk($this->items, $size);
    }

    public function contains(\Closure|string $key, mixed $value = null): bool
    {
        if ($key instanceof \Closure) {
            foreach ($this->items as $item) {
                if ($key($item)) return true;
            }
            return false;
        }
        return in_array($value, $this->pluck($key));
    }

    // ArrayAccess
    public function offsetExists(mixed $offset): bool        { return isset($this->items[$offset]); }
    public function offsetGet(mixed $offset): mixed          { return $this->items[$offset]; }
    public function offsetSet(mixed $offset, mixed $value): void { $this->items[$offset] = $value; }
    public function offsetUnset(mixed $offset): void         { unset($this->items[$offset]); }

    // IteratorAggregate
    public function getIterator(): \ArrayIterator { return new \ArrayIterator($this->items); }
}