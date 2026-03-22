<?php

declare(strict_types=1);

namespace Core\AI\Sql;

use RuntimeException;

class SqlGuard
{
    /** @param array<string, mixed> $config */
    public function __construct(
        private readonly array $config = [],
    ) {}

    /**
     * @param list<string> $knownTables
     * @return array{sql:string, limit:int}
     */
    public function protect(string $sql, array $knownTables = []): array
    {
        $candidate = trim($sql);
        $candidate = trim($candidate, " \t\n\r\0\x0B;");
        $candidate = $this->normalizeAggregateAliases($candidate);
        $normalized = strtolower($candidate);

        if ($candidate === '') {
            throw new RuntimeException('Generated SQL is empty.');
        }

        if (!str_starts_with($normalized, 'select ')) {
            throw new RuntimeException('Only SELECT queries are allowed.');
        }

        if (str_contains($normalized, ';')) {
            throw new RuntimeException('Multiple statements are not allowed.');
        }

        $this->assertNoWildcardSelect($candidate);

        foreach ($this->denyKeywords() as $keyword) {
            if (str_contains($normalized, strtolower($keyword))) {
                throw new RuntimeException('Blocked keyword detected in SQL: ' . $keyword);
            }
        }

        $tables = $this->extractTables($normalized);
        $this->assertAllowedTables($tables, $knownTables);

        [$finalSql, $limit] = $this->enforceLimit($candidate);

        return [
            'sql' => $finalSql,
            'limit' => $limit,
        ];
    }

    /** @return list<string> */
    private function denyKeywords(): array
    {
        $defaults = [
            'insert', 'update', 'delete', 'drop', 'alter', 'truncate',
            'create', 'replace', 'grant', 'revoke', 'call', 'execute',
            'into outfile', 'load_file', 'attach database', 'pragma',
        ];

        $configured = $this->config['deny_keywords'] ?? $defaults;

        return is_array($configured) ? array_values(array_map('strval', $configured)) : $defaults;
    }

    /** @return list<string> */
    private function extractTables(string $normalizedSql): array
    {
        preg_match_all('/\bfrom\s+([a-z0-9_\.`]+)|\bjoin\s+([a-z0-9_\.`]+)/i', $normalizedSql, $matches);
        $raw = array_merge($matches[1] ?? [], $matches[2] ?? []);
        $tables = [];
        foreach ($raw as $table) {
            $clean = trim($table, " `\t\n\r\0\x0B");
            if ($clean === '') {
                continue;
            }
            $pieces = explode('.', $clean);
            $tables[] = end($pieces) ?: $clean;
        }

        return array_values(array_unique($tables));
    }

    /** @param list<string> $tables * @param list<string> $knownTables */
    private function assertAllowedTables(array $tables, array $knownTables): void
    {
        $allowRaw = (string) ($this->config['allow_tables'] ?? '*');
        if ($allowRaw !== '*') {
            $allowList = array_values(array_filter(array_map('trim', explode(',', $allowRaw))));
            if ($allowList === []) {
                throw new RuntimeException('AI SQL allow list is empty.');
            }

            foreach ($tables as $table) {
                if (!in_array($table, $allowList, true)) {
                    throw new RuntimeException('Table is not allowed for AI SQL: ' . $table);
                }
            }
        }

        if ($knownTables === []) {
            return;
        }

        foreach ($tables as $table) {
            if (!in_array($table, $knownTables, true)) {
                throw new RuntimeException('Table not found in known schema: ' . $table);
            }
        }
    }

    /** @return array{0:string,1:int} */
    private function enforceLimit(string $sql): array
    {
        $defaultLimit = max(1, (int) ($this->config['default_limit'] ?? 100));
        $maxRows = max($defaultLimit, (int) ($this->config['max_rows'] ?? 200));
        $normalized = strtolower($sql);

        if (str_contains($normalized, 'count(')) {
            if (preg_match('/\blimit\s+(\d+)/i', $sql, $match)) {
                $requested = (int) ($match[1] ?? 1);
                $safeLimit = max(1, min($requested, $maxRows));
                $safeSql = (string) preg_replace('/\blimit\s+\d+/i', 'LIMIT ' . $safeLimit, $sql, 1);

                return [$safeSql, $safeLimit];
            }

            return [$sql, 1];
        }

        if (!preg_match('/\blimit\s+(\d+)/i', $sql, $match)) {
            return [$sql . ' LIMIT ' . $defaultLimit, $defaultLimit];
        }

        $requested = (int) ($match[1] ?? $defaultLimit);
        $safeLimit = max(1, min($requested, $maxRows));
        $safeSql = (string) preg_replace('/\blimit\s+\d+/i', 'LIMIT ' . $safeLimit, $sql, 1);

        return [$safeSql, $safeLimit];
    }

    private function normalizeAggregateAliases(string $sql): string
    {
        return (string) preg_replace_callback(
            '/\bcount\s*\(\s*[\*\w`\.]+\s*\)(?!\s+as\s+[a-z0-9_`]+)/i',
            static fn (array $m): string => strtoupper((string) $m[0]) . ' AS total',
            $sql,
            1
        );
    }

    private function assertNoWildcardSelect(string $sql): void
    {
        if (!preg_match('/^\s*select\s+(.*?)\s+from\s+/is', $sql, $match)) {
            return;
        }

        $selectClause = strtolower((string) ($match[1] ?? ''));
        if ($selectClause === '') {
            return;
        }

        // count(*) allowed; other wildcard usage blocked.
        $withoutCountStar = (string) preg_replace('/count\s*\(\s*\*\s*\)/i', '', $selectClause);
        if (str_contains($withoutCountStar, '*')) {
            throw new RuntimeException('Wildcard select is not allowed. Select explicit columns.');
        }
    }
}
