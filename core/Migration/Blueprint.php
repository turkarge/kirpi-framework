<?php

declare(strict_types=1);

namespace Core\Migration;

class Blueprint
{
    private array $columns  = [];
    private array $indexes  = [];
    private array $foreigns = [];
    private array $commands = [];

    public function __construct(
        private readonly string $table,
        private readonly bool   $creating = true,
    ) {}

    // ─── Kolon Tipleri ───────────────────────────────────────

    public function id(string $name = 'id'): ColumnDefinition
    {
        return $this->addColumn('bigIncrements', $name)
                    ->unsigned()
                    ->autoIncrement();
    }

    public function uuid(string $name = 'uuid'): ColumnDefinition
    {
        return $this->addColumn('uuid', $name);
    }

    public function string(string $name, int $length = 255): ColumnDefinition
    {
        return $this->addColumn('string', $name, compact('length'));
    }

    public function text(string $name): ColumnDefinition
    {
        return $this->addColumn('text', $name);
    }

    public function longText(string $name): ColumnDefinition
    {
        return $this->addColumn('longText', $name);
    }

    public function integer(string $name): ColumnDefinition
    {
        return $this->addColumn('integer', $name);
    }

    public function unsignedInteger(string $name): ColumnDefinition
    {
        return $this->addColumn('unsignedInteger', $name);
    }

    public function bigInteger(string $name): ColumnDefinition
    {
        return $this->addColumn('bigInteger', $name);
    }

    public function unsignedBigInteger(string $name): ColumnDefinition
    {
        return $this->addColumn('unsignedBigInteger', $name);
    }

    public function tinyInteger(string $name): ColumnDefinition
    {
        return $this->addColumn('tinyInteger', $name);
    }

    public function boolean(string $name): ColumnDefinition
    {
        return $this->addColumn('boolean', $name);
    }

    public function decimal(string $name, int $precision = 8, int $scale = 2): ColumnDefinition
    {
        return $this->addColumn('decimal', $name, compact('precision', 'scale'));
    }

    public function float(string $name): ColumnDefinition
    {
        return $this->addColumn('float', $name);
    }

    public function json(string $name): ColumnDefinition
    {
        return $this->addColumn('json', $name);
    }

    public function date(string $name): ColumnDefinition
    {
        return $this->addColumn('date', $name);
    }

    public function dateTime(string $name): ColumnDefinition
    {
        return $this->addColumn('dateTime', $name);
    }

    public function timestamp(string $name): ColumnDefinition
    {
        return $this->addColumn('timestamp', $name);
    }

    public function timestamps(): void
    {
        $this->timestamp('created_at')->nullable();
        $this->timestamp('updated_at')->nullable();
    }

    public function softDeletes(): void
    {
        $this->timestamp('deleted_at')->nullable();
    }

    public function enum(string $name, array $values): ColumnDefinition
    {
        return $this->addColumn('enum', $name, compact('values'));
    }

    public function foreignId(string $name): ColumnDefinition
    {
        return $this->unsignedBigInteger($name);
    }

    // ─── Index'ler ───────────────────────────────────────────

    public function primary(string|array $columns): static
    {
        $this->indexes[] = ['type' => 'primary', 'columns' => (array) $columns];
        return $this;
    }

    public function unique(string|array $columns, ?string $name = null): static
    {
        $cols = (array) $columns;
        $this->indexes[] = [
            'type'    => 'unique',
            'columns' => $cols,
            'name'    => $name ?? $this->table . '_' . implode('_', $cols) . '_unique',
        ];
        return $this;
    }

    public function index(string|array $columns, ?string $name = null): static
    {
        $cols = (array) $columns;
        $this->indexes[] = [
            'type'    => 'index',
            'columns' => $cols,
            'name'    => $name ?? $this->table . '_' . implode('_', $cols) . '_index',
        ];
        return $this;
    }

    // ─── Foreign Key ─────────────────────────────────────────

    public function foreign(string $column): ForeignKeyDefinition
    {
        $foreign = new ForeignKeyDefinition($column, $this->table);
        $this->foreigns[] = $foreign;
        return $foreign;
    }

    // ─── Alter Komutları ─────────────────────────────────────

    public function dropColumn(string ...$columns): static
    {
        $this->commands[] = ['type' => 'dropColumn', 'columns' => $columns];
        return $this;
    }

    public function renameColumn(string $from, string $to): static
    {
        $this->commands[] = ['type' => 'renameColumn', 'from' => $from, 'to' => $to];
        return $this;
    }

    public function dropIndex(string $name): static
    {
        $this->commands[] = ['type' => 'dropIndex', 'name' => $name];
        return $this;
    }

    public function dropForeign(string $name): static
    {
        $this->commands[] = ['type' => 'dropForeign', 'name' => $name];
        return $this;
    }

    // ─── Getters ─────────────────────────────────────────────

    public function getTable(): string      { return $this->table; }
    public function isCreating(): bool      { return $this->creating; }
    public function getColumns(): array     { return $this->columns; }
    public function getIndexes(): array     { return $this->indexes; }
    public function getForeigns(): array    { return $this->foreigns; }
    public function getCommands(): array    { return $this->commands; }

    // ─── Private ─────────────────────────────────────────────

    private function addColumn(string $type, string $name, array $params = []): ColumnDefinition
    {
        $column          = new ColumnDefinition($type, $name, $params);
        $this->columns[] = $column;
        return $column;
    }
}