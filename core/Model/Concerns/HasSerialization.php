<?php

declare(strict_types=1);

namespace Core\Model\Concerns;

trait HasSerialization
{
    public function toArray(): array
    {
        $attributes = $this->attributes;

        // Hidden alanları çıkar
        foreach ($this->hidden as $key) {
            unset($attributes[$key]);
        }

        // Cast'leri uygula
        foreach (array_keys($attributes) as $key) {
            if (isset($this->casts[$key])) {
                $attributes[$key] = $this->castGet($key, $attributes[$key]);
            }
        }

        // Accessor'ları ekle
        foreach ($this->appends as $key) {
            $attributes[$key] = $this->getAttribute($key);
        }

        // İlişkileri ekle
        foreach ($this->relations as $key => $value) {
            if ($value instanceof \Core\Database\Result\Collection) {
                $attributes[$key] = array_map(
                    fn($m) => method_exists($m, 'toArray') ? $m->toArray() : (array) $m,
                    $value->toArray()
                );
            } else {
                $attributes[$key] = method_exists($value, 'toArray')
                    ? $value->toArray()
                    : (array) $value;
            }
        }

        return $attributes;
    }

    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_UNESCAPED_UNICODE);
    }

    public function makeHidden(string ...$keys): static
    {
        array_push($this->hidden, ...$keys);
        return $this;
    }

    public function makeVisible(string ...$keys): static
    {
        $this->hidden = array_diff($this->hidden, $keys);
        return $this;
    }
}