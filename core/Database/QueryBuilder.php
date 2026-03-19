<?php

declare(strict_types=1);

namespace Core\Database;

use Core\Database\Contracts\DriverInterface;
use Core\Database\Result\Collection;
use Core\Database\Result\Paginator;

class QueryBuilder
{
    private array   $columns   = ['*'];
    private array   $wheres    = [];
    private array   $joins     = [];
    private array   $orders    = [];
    private array   $groups    = [];
    private array   $havings   = [];
    private array   $bindings  = [];
    private ?int    $limitVal  = null;
    private ?int    $offsetVal = null;
    private bool    $distinct  = false;
    private ?string $lockMode  = null;
    private ?string      $modelClass = null;

    public function __construct(
        private readonly DriverInterface $driver,
        private string $table = '',
    ) {}

    // ─── Select ──────────────────────────────────────────────

    public function select(string ...$columns): static
    {
        $this->columns = $columns;
        return $this;
    }

    public function selectRaw(string $expression, array $bindings = []): static
    {
        $this->columns[] = $expression;
        array_push($this->bindings, ...$bindings);
        return $this;
    }

    public function addSelect(string ...$columns): static
    {
        array_push($this->columns, ...$columns);
        return $this;
    }

    public function distinct(): static
    {
        $this->distinct = true;
        return $this;
    }

    // ─── Where ───────────────────────────────────────────────

    public function where(
        string|array|\Closure $column,
        mixed $operator = null,
        mixed $value    = null,
        string $boolean = 'AND'
    ): static {
        if (is_array($column)) {
            foreach ($column as $col => $val) {
                $this->where($col, '=', $val, $boolean);
            }
            return $this;
        }

        if ($column instanceof \Closure) {
            $nested = new static($this->driver, $this->table);
            $column($nested);
            $this->wheres[]  = ['type' => 'nested', 'query' => $nested, 'boolean' => $boolean];
            array_push($this->bindings, ...$nested->bindings);
            return $this;
        }

        if ($value === null && $operator !== null) {
            [$value, $operator] = [$operator, '='];
        }

        $this->wheres[]   = compact('column', 'operator', 'value', 'boolean');
        $this->bindings[] = $value;

        return $this;
    }

    public function orWhere(string|\Closure $column, mixed $operator = null, mixed $value = null): static
    {
        return $this->where($column, $operator, $value, 'OR');
    }

    public function whereNull(string $column, string $boolean = 'AND', bool $not = false): static
    {
        $this->wheres[] = ['type' => $not ? 'notNull' : 'null', 'column' => $column, 'boolean' => $boolean];
        return $this;
    }

    public function whereNotNull(string $column): static
    {
        return $this->whereNull($column, 'AND', true);
    }

    public function whereIn(string $column, array $values, string $boolean = 'AND', bool $not = false): static
    {
        $this->wheres[] = ['type' => $not ? 'notIn' : 'in', 'column' => $column, 'values' => $values, 'boolean' => $boolean];
        array_push($this->bindings, ...$values);
        return $this;
    }

    public function whereNotIn(string $column, array $values): static
    {
        return $this->whereIn($column, $values, 'AND', true);
    }

    public function whereBetween(string $column, array $values, string $boolean = 'AND', bool $not = false): static
    {
        $this->wheres[] = ['type' => $not ? 'notBetween' : 'between', 'column' => $column, 'boolean' => $boolean];
        array_push($this->bindings, $values[0], $values[1]);
        return $this;
    }

    public function whereNotBetween(string $column, array $values): static
    {
        return $this->whereBetween($column, $values, 'AND', true);
    }

    public function whereLike(string $column, string $value): static
    {
        return $this->where($column, 'LIKE', $value);
    }

    public function whereRaw(string $sql, array $bindings = []): static
    {
        $this->wheres[] = ['type' => 'raw', 'sql' => $sql, 'boolean' => 'AND'];
        array_push($this->bindings, ...$bindings);
        return $this;
    }

    public function when(bool $condition, \Closure $callback, ?\Closure $default = null): static
    {
        if ($condition) {
            $callback($this);
        } elseif ($default !== null) {
            $default($this);
        }
        return $this;
    }

    // ─── Join ────────────────────────────────────────────────

    public function join(string $table, string $first, string $operator, string $second, string $type = 'INNER'): static
    {
        $this->joins[] = compact('table', 'first', 'operator', 'second', 'type');
        return $this;
    }

    public function leftJoin(string $table, string $first, string $operator, string $second): static
    {
        return $this->join($table, $first, $operator, $second, 'LEFT');
    }

    public function rightJoin(string $table, string $first, string $operator, string $second): static
    {
        return $this->join($table, $first, $operator, $second, 'RIGHT');
    }

    // ─── Order / Group / Limit ───────────────────────────────

    public function orderBy(string $column, string $direction = 'ASC'): static
    {
        $this->orders[] = compact('column', 'direction');
        return $this;
    }

    public function orderByDesc(string $column): static
    {
        return $this->orderBy($column, 'DESC');
    }

    public function groupBy(string ...$columns): static
    {
        array_push($this->groups, ...$columns);
        return $this;
    }

    public function having(string $column, mixed $operator, mixed $value = null): static
    {
        if ($value === null) {
            [$value, $operator] = [$operator, '='];
        }
        $this->havings[]  = compact('column', 'operator', 'value');
        $this->bindings[] = $value;
        return $this;
    }

    public function limit(int $value): static
    {
        $this->limitVal = $value;
        return $this;
    }

    public function offset(int $value): static
    {
        $this->offsetVal = $value;
        return $this;
    }

    public function forPage(int $page, int $perPage = 15): static
    {
        return $this->offset(($page - 1) * $perPage)->limit($perPage);
    }

    public function forUpdate(): static
    {
        $this->lockMode = 'FOR UPDATE';
        return $this;
    }

    // ─── Executors ───────────────────────────────────────────

    public function get(): Collection
    {
        $sql     = $this->compileSelect();
        $results = $this->driver->select($sql, $this->bindings);
        return new Collection($results, $this->modelClass);
    }

    public function first(): ?object
    {
        return $this->limit(1)->get()->first();
    }

    public function firstOrFail(): object
    {
        return $this->first()
            ?? throw new \Core\Database\Exceptions\DatabaseException(
                "No record found in [{$this->table}]."
            );
    }

    public function find(int|string $id, string $column = 'id'): ?object
    {
        return $this->where($column, $id)->first();
    }

    public function findOrFail(int|string $id, string $column = 'id'): object
    {
        return $this->find($id, $column)
            ?? throw new \Core\Exception\NotFoundException(
                "Record [{$id}] not found in [{$this->table}]."
            );
    }

    public function paginate(int $perPage = 15, ?int $page = null): Paginator
    {
        $page  ??= (int) ($_GET['page'] ?? 1);
        $total  = $this->count();
        $items  = $this->forPage($page, $perPage)->get();
        return new Paginator($items, $total, $perPage, $page);
    }

    public function count(string $column = '*'): int
    {
        $clone            = clone $this;
        $clone->columns   = ["COUNT({$column}) as aggregate"];
        $clone->orders    = [];
        $clone->limitVal  = null;
        $clone->offsetVal = null;

        $result = $clone->driver->select($clone->compileSelect(), $clone->bindings);
        return (int) ($result[0]->aggregate ?? 0);
    }

    public function exists(): bool  { return $this->count() > 0; }
    public function doesntExist(): bool { return !$this->exists(); }

    public function max(string $column): mixed { return $this->aggregate('MAX', $column); }
    public function min(string $column): mixed { return $this->aggregate('MIN', $column); }
    public function sum(string $column): mixed { return $this->aggregate('SUM', $column); }
    public function avg(string $column): mixed { return $this->aggregate('AVG', $column); }

    public function pluck(string $column): array
    {
        return $this->get()->pluck($column);
    }

    // ─── Write ───────────────────────────────────────────────

    public function insert(array $data): int
    {
        $rows    = isset($data[0]) && is_array($data[0]) ? $data : [$data];
        $columns = array_keys($rows[0]);
        $allValues = [];

        $valueSets = array_map(function ($row) use ($columns, &$allValues) {
            foreach ($columns as $col) {
                $allValues[] = $row[$col] ?? null;
            }
            return '(' . implode(', ', array_fill(0, count($columns), '?')) . ')';
        }, $rows);

        $cols = implode(', ', array_map(fn($c) => "`{$c}`", $columns));
        $vals = implode(', ', $valueSets);
        $sql  = "INSERT INTO `{$this->table}` ({$cols}) VALUES {$vals}";

        return $this->driver->insert($sql, $allValues);
    }

    public function update(array $data): int
    {
        $set      = implode(', ', array_map(fn($col) => "`{$col}` = ?", array_keys($data)));
        $sql      = "UPDATE `{$this->table}` SET {$set}" . $this->compileWheres();
        $bindings = array_merge(array_values($data), $this->bindings);

        return $this->driver->update($sql, $bindings);
    }

    public function updateOrInsert(array $conditions, array $data): bool
    {
        if ($this->where($conditions)->exists()) {
            return (bool) $this->where($conditions)->update($data);
        }
        return (bool) $this->insert(array_merge($conditions, $data));
    }

    public function delete(): int
    {
        $sql = "DELETE FROM `{$this->table}`" . $this->compileWheres();
        return $this->driver->delete($sql, $this->bindings);
    }

    public function increment(string $column, int $amount = 1, array $extra = []): int
    {
        $sets     = ["`{$column}` = `{$column}` + {$amount}"];
        $extraVals = [];

        foreach ($extra as $col => $val) {
            $sets[]      = "`{$col}` = ?";
            $extraVals[] = $val;
        }

        $sql = "UPDATE `{$this->table}` SET " . implode(', ', $sets) . $this->compileWheres();
        return $this->driver->update($sql, array_merge($extraVals, $this->bindings));
    }

    public function decrement(string $column, int $amount = 1, array $extra = []): int
    {
        return $this->increment($column, -$amount, $extra);
    }

    public function chunk(int $size, \Closure $callback): void
    {
        $page = 1;
        do {
            $results = $this->forPage($page, $size)->get();
            if ($results->isEmpty()) break;
            $callback($results);
            $page++;
        } while ($results->count() === $size);
    }

    // ─── Model ───────────────────────────────────────────────

    public function setModel(string $class): static
    {
        $this->modelClass = $class;
        return $this;
    }

    // ─── Debug ───────────────────────────────────────────────

    public function toSql(): string  { return $this->compileSelect(); }

    public function dump(): static
    {
        dump($this->toSql(), $this->bindings);
        return $this;
    }

    public function dd(): never
    {
        dd($this->toSql(), $this->bindings);
    }

    // ─── Compiler ────────────────────────────────────────────

    private function compileSelect(): string
    {
        $sql  = $this->distinct ? 'SELECT DISTINCT ' : 'SELECT ';
        $sql .= implode(', ', $this->columns);
        $sql .= " FROM `{$this->table}`";
        $sql .= $this->compileJoins();
        $sql .= $this->compileWheres();
        $sql .= $this->compileGroups();
        $sql .= $this->compileHavings();
        $sql .= $this->compileOrders();
        $sql .= $this->compileLimitOffset();

        if ($this->lockMode) {
            $sql .= " {$this->lockMode}";
        }

        return $sql;
    }

    private function compileJoins(): string
    {
        if (empty($this->joins)) return '';

        return ' ' . implode(' ', array_map(function ($join) {
            return "{$join['type']} JOIN `{$join['table']}` ON {$join['first']} {$join['operator']} {$join['second']}";
        }, $this->joins));
    }

    private function compileWheres(): string
    {
        if (empty($this->wheres)) return '';

        $parts = [];

        foreach ($this->wheres as $i => $where) {
            $boolean = $i === 0 ? '' : " {$where['boolean']} ";

            $parts[] = match($where['type'] ?? 'basic') {
                'nested'     => $boolean . '(' . $this->compileNestedWheres($where['query']) . ')',
                'null'       => $boolean . "`{$where['column']}` IS NULL",
                'notNull'    => $boolean . "`{$where['column']}` IS NOT NULL",
                'in'         => $boolean . "`{$where['column']}` IN (" . implode(', ', array_fill(0, count($where['values']), '?')) . ")",
                'notIn'      => $boolean . "`{$where['column']}` NOT IN (" . implode(', ', array_fill(0, count($where['values']), '?')) . ")",
                'between'    => $boolean . "`{$where['column']}` BETWEEN ? AND ?",
                'notBetween' => $boolean . "`{$where['column']}` NOT BETWEEN ? AND ?",
                'raw'        => $boolean . $where['sql'],
                default      => $boolean . "`{$where['column']}` {$where['operator']} ?",
            };
        }

        return ' WHERE ' . implode('', $parts);
    }

    private function compileNestedWheres(QueryBuilder $nested): string
    {
        if (empty($nested->wheres)) return '1=1';

        $parts = [];
        foreach ($nested->wheres as $i => $where) {
            $boolean = $i === 0 ? '' : " {$where['boolean']} ";
            $parts[] = match($where['type'] ?? 'basic') {
                'null'    => $boolean . "`{$where['column']}` IS NULL",
                'notNull' => $boolean . "`{$where['column']}` IS NOT NULL",
                'raw'     => $boolean . $where['sql'],
                default   => $boolean . "`{$where['column']}` {$where['operator']} ?",
            };
        }

        return implode('', $parts);
    }

    private function compileGroups(): string
    {
        if (empty($this->groups)) return '';
        return ' GROUP BY ' . implode(', ', array_map(fn($g) => "`{$g}`", $this->groups));
    }

    private function compileHavings(): string
    {
        if (empty($this->havings)) return '';
        $parts = array_map(fn($h) => "`{$h['column']}` {$h['operator']} ?", $this->havings);
        return ' HAVING ' . implode(' AND ', $parts);
    }

    private function compileOrders(): string
    {
        if (empty($this->orders)) return '';
        $parts = array_map(fn($o) => "`{$o['column']}` {$o['direction']}", $this->orders);
        return ' ORDER BY ' . implode(', ', $parts);
    }

    private function compileLimitOffset(): string
    {
        $sql = '';
        if ($this->limitVal !== null)  $sql .= " LIMIT {$this->limitVal}";
        if ($this->offsetVal !== null) $sql .= " OFFSET {$this->offsetVal}";
        return $sql;
    }

    private function aggregate(string $function, string $column): mixed
    {
        $clone          = clone $this;
        $clone->columns = ["{$function}(`{$column}`) as aggregate"];
        $result = $clone->driver->select($clone->compileSelect(), $clone->bindings);
        return $result[0]->aggregate ?? null;
    }
}