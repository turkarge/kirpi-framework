<?php

declare(strict_types=1);

namespace Core\Database\Result;

class Paginator
{
    private int $lastPage;

    public function __construct(
        private readonly Collection $items,
        private readonly int        $total,
        private readonly int        $perPage,
        private readonly int        $currentPage,
    ) {
        $this->lastPage = (int) ceil($total / max($perPage, 1));
    }

    public function items(): Collection  { return $this->items; }
    public function total(): int         { return $this->total; }
    public function perPage(): int       { return $this->perPage; }
    public function currentPage(): int   { return $this->currentPage; }
    public function lastPage(): int      { return $this->lastPage; }
    public function hasPages(): bool     { return $this->lastPage > 1; }
    public function hasMorePages(): bool { return $this->currentPage < $this->lastPage; }
    public function onFirstPage(): bool  { return $this->currentPage === 1; }
    public function isEmpty(): bool      { return $this->total === 0; }
    public function isNotEmpty(): bool   { return !$this->isEmpty(); }

    public function from(): int
    {
        return $this->isEmpty() ? 0 : ($this->currentPage - 1) * $this->perPage + 1;
    }

    public function to(): int
    {
        return min($this->currentPage * $this->perPage, $this->total);
    }

    public function toArray(): array
    {
        return [
            'data'         => $this->items->toArray(),
            'total'        => $this->total,
            'per_page'     => $this->perPage,
            'current_page' => $this->currentPage,
            'last_page'    => $this->lastPage,
            'has_more'     => $this->hasMorePages(),
            'from'         => $this->from(),
            'to'           => $this->to(),
        ];
    }

    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_UNESCAPED_UNICODE);
    }
}