<?php

declare(strict_types=1);

namespace Core\AI\Sql;

use Core\Database\DatabaseManager;

class SchemaInspector
{
    public function __construct(
        private readonly DatabaseManager $db,
    ) {}

    /**
     * @return array<int, array{name:string, columns:list<string>}>
     */
    public function schemaSummary(): array
    {
        return match ((string) config('database.default', 'mysql')) {
            'sqlite' => $this->sqliteSchema(),
            default => $this->mysqlSchema(),
        };
    }

    /** @return list<string> */
    public function tableNames(): array
    {
        return array_map(static fn (array $table): string => $table['name'], $this->schemaSummary());
    }

    /** @return array<int, array{name:string, columns:list<string>}> */
    private function mysqlSchema(): array
    {
        $databaseName = (string) config('database.connections.mysql.database', '');
        $rows = $this->db->raw(
            'SELECT TABLE_NAME as table_name, COLUMN_NAME as column_name
             FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = ?
             ORDER BY TABLE_NAME, ORDINAL_POSITION',
            [$databaseName]
        );

        $tables = [];
        foreach ($rows as $row) {
            $tableName = (string) $this->rowValue($row, 'table_name');
            $columnName = (string) $this->rowValue($row, 'column_name');
            if ($tableName === '' || $columnName === '') {
                continue;
            }
            $tables[$tableName] ??= [];
            $tables[$tableName][] = $columnName;
        }

        return array_map(
            static fn (string $name, array $columns): array => ['name' => $name, 'columns' => array_values(array_unique($columns))],
            array_keys($tables),
            array_values($tables)
        );
    }

    /** @return array<int, array{name:string, columns:list<string>}> */
    private function sqliteSchema(): array
    {
        $tablesRows = $this->db->raw("SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%' ORDER BY name");
        $summary = [];
        foreach ($tablesRows as $row) {
            $tableName = (string) $this->rowValue($row, 'name');
            if ($tableName === '') {
                continue;
            }
            $columnsRows = $this->db->raw("PRAGMA table_info(" . $tableName . ')');
            $columns = [];
            foreach ($columnsRows as $column) {
                $columns[] = (string) $this->rowValue($column, 'name');
            }
            $summary[] = [
                'name' => $tableName,
                'columns' => array_values(array_filter($columns)),
            ];
        }

        return $summary;
    }

    private function rowValue(mixed $row, string $key): mixed
    {
        if (is_array($row)) {
            return $row[$key] ?? null;
        }

        if (is_object($row) && isset($row->{$key})) {
            return $row->{$key};
        }

        return null;
    }
}
