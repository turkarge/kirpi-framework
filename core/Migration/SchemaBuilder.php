<?php

declare(strict_types=1);

namespace Core\Migration;

use Core\Database\DatabaseManager;

class SchemaBuilder
{
    public function __construct(
        private readonly DatabaseManager $db,
    ) {}

    public function create(string $table, \Closure $callback): void
{
    $blueprint = new Blueprint($table, creating: true);
    $callback($blueprint);

    $sql = $this->compileCreate($blueprint);
    
    // Debug
    error_log("SQL: " . $sql);
    
    $this->db->connection()->statement($sql);
    
    error_log("Table created: " . $table);
}

    public function table(string $table, \Closure $callback): void
    {
        $blueprint = new Blueprint($table, creating: false);
        $callback($blueprint);

        foreach ($this->compileAlter($blueprint) as $sql) {
            $this->db->connection()->statement($sql);
        }
    }

    public function drop(string $table): void
    {
        $this->db->connection()->statement("DROP TABLE `{$table}`");
    }

    public function dropIfExists(string $table): void
    {
        $this->db->connection()->statement("DROP TABLE IF EXISTS `{$table}`");
    }

    public function hasTable(string $table): bool
    {
        $database = env('DB_DATABASE');
        $result   = $this->db->raw(
            "SELECT COUNT(*) as count FROM information_schema.tables
             WHERE table_schema = ? AND table_name = ?",
            [$database, $table]
        );

        return (int) ($result[0]->count ?? 0) > 0;
    }

    public function hasColumn(string $table, string $column): bool
    {
        $database = env('DB_DATABASE');
        $result   = $this->db->raw(
            "SELECT COUNT(*) as count FROM information_schema.columns
             WHERE table_schema = ? AND table_name = ? AND column_name = ?",
            [$database, $table, $column]
        );

        return (int) ($result[0]->count ?? 0) > 0;
    }

    // ─── Compiler ────────────────────────────────────────────

    private function compileCreate(Blueprint $blueprint): string
    {
        $definitions = array_filter(array_merge(
            $this->compileColumns($blueprint->getColumns()),
            $this->compileIndexes($blueprint->getIndexes()),
            $this->compileForeigns($blueprint->getForeigns()),
        ));

        return sprintf(
            "CREATE TABLE `%s` (\n  %s\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            $blueprint->getTable(),
            implode(",\n  ", $definitions)
        );
    }

    private function compileAlter(Blueprint $blueprint): array
    {
        $statements = [];
        $table      = $blueprint->getTable();

        foreach ($blueprint->getColumns() as $column) {
            $statements[] = "ALTER TABLE `{$table}` ADD COLUMN " . $this->compileColumn($column);
        }

        foreach ($blueprint->getIndexes() as $index) {
            $statements[] = $this->compileAddIndex($table, $index);
        }

        foreach ($blueprint->getForeigns() as $foreign) {
            $statements[] = $this->compileAddForeign($table, $foreign);
        }

        foreach ($blueprint->getCommands() as $command) {
            $statements[] = match($command['type']) {
                'dropColumn'   => "ALTER TABLE `{$table}` DROP COLUMN `{$command['columns'][0]}`",
                'renameColumn' => "ALTER TABLE `{$table}` RENAME COLUMN `{$command['from']}` TO `{$command['to']}`",
                'dropIndex'    => "ALTER TABLE `{$table}` DROP INDEX `{$command['name']}`",
                'dropForeign'  => "ALTER TABLE `{$table}` DROP FOREIGN KEY `{$command['name']}`",
                default        => throw new \RuntimeException("Unknown command: {$command['type']}"),
            };
        }

        return $statements;
    }

    private function compileColumns(array $columns): array
    {
        return array_map(fn($col) => $this->compileColumn($col), $columns);
    }

    private function compileColumn(ColumnDefinition $col): string
    {
        $sql = "`{$col->getName()}` " . $this->mapType($col);

        if ($col->get('unsigned'))        $sql .= ' UNSIGNED';
        if ($col->get('autoIncrement'))   $sql .= ' AUTO_INCREMENT';
        if (!$col->get('nullable'))       $sql .= ' NOT NULL';
        else                              $sql .= ' NULL';

        if ($col->get('default') !== null) {
            $default = is_string($col->get('default'))
                ? "'{$col->get('default')}'"
                : $col->get('default');
            $sql .= " DEFAULT {$default}";
        }

        if ($col->get('comment')) $sql .= " COMMENT '{$col->get('comment')}'";
        if ($col->get('after'))   $sql .= " AFTER `{$col->get('after')}`";

        return $sql;
    }

    private function mapType(ColumnDefinition $col): string
    {
        return match($col->getType()) {
            'bigIncrements'      => 'BIGINT',
            'unsignedBigInteger' => 'BIGINT',
            'bigInteger'         => 'BIGINT',
            'unsignedInteger'    => 'INT',
            'integer'            => 'INT',
            'tinyInteger'        => 'TINYINT',
            'boolean'            => 'TINYINT(1)',
            'string'             => 'VARCHAR(' . $col->get('length', 255) . ')',
            'text'               => 'TEXT',
            'longText'           => 'LONGTEXT',
            'decimal'            => "DECIMAL({$col->get('precision', 8)}, {$col->get('scale', 2)})",
            'float'              => 'FLOAT',
            'json'               => 'JSON',
            'date'               => 'DATE',
            'dateTime'           => 'DATETIME',
            'timestamp'          => 'TIMESTAMP',
            'uuid'               => 'CHAR(36)',
            'enum'               => 'ENUM(' . implode(',', array_map(fn($v) => "'{$v}'", $col->get('values', []))) . ')',
            default              => strtoupper($col->getType()),
        };
    }

    private function compileIndexes(array $indexes): array
    {
        return array_map(function ($index) {
            $columns = '`' . implode('`, `', $index['columns']) . '`';

            return match($index['type']) {
                'primary'  => "PRIMARY KEY ({$columns})",
                'unique'   => "UNIQUE KEY `{$index['name']}` ({$columns})",
                'index'    => "KEY `{$index['name']}` ({$columns})",
                'fulltext' => "FULLTEXT KEY ({$columns})",
                default    => '',
            };
        }, $indexes);
    }

    private function compileForeigns(array $foreigns): array
    {
        return array_map(function (ForeignKeyDefinition $foreign) {
            $def = $foreign->toArray();
            return "CONSTRAINT `{$def['name']}` FOREIGN KEY (`{$def['column']}`) " .
                   "REFERENCES `{$def['on']}` (`{$def['references']}`) " .
                   "ON DELETE {$def['onDelete']} ON UPDATE {$def['onUpdate']}";
        }, $foreigns);
    }

    private function compileAddIndex(string $table, array $index): string
    {
        $columns = '`' . implode('`, `', $index['columns']) . '`';

        return match($index['type']) {
            'unique' => "ALTER TABLE `{$table}` ADD UNIQUE KEY `{$index['name']}` ({$columns})",
            default  => "ALTER TABLE `{$table}` ADD KEY `{$index['name']}` ({$columns})",
        };
    }

    private function compileAddForeign(string $table, ForeignKeyDefinition $foreign): string
    {
        $def = $foreign->toArray();
        return "ALTER TABLE `{$table}` ADD CONSTRAINT `{$def['name']}` " .
               "FOREIGN KEY (`{$def['column']}`) REFERENCES `{$def['on']}` (`{$def['references']}`) " .
               "ON DELETE {$def['onDelete']} ON UPDATE {$def['onUpdate']}";
    }
}