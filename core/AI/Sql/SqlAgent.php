<?php

declare(strict_types=1);

namespace Core\AI\Sql;

use Core\AI\AiManager;
use Core\AI\Trace\AiTraceLogger;
use Core\Database\DatabaseManager;
use RuntimeException;

class SqlAgent
{
    public function __construct(
        private readonly AiManager $ai,
        private readonly DatabaseManager $db,
        private readonly SchemaInspector $schemaInspector,
        private readonly SqlGuard $sqlGuard,
        private readonly AiTraceLogger $traceLogger,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function ask(string $question, ?string $model = null): array
    {
        $startedAt = microtime(true);
        $question = trim($question);
        if ($question === '') {
            throw new RuntimeException('Question cannot be empty.');
        }

        $schema = $this->schemaInspector->schemaSummary();
        if ($schema === []) {
            throw new RuntimeException('Schema metadata is empty. Cannot generate SQL.');
        }

        $prompt = $this->buildPrompt($question, $schema);
        $options = [
            'system' => 'You are a SQL generator. Return only one SELECT statement without explanation.',
        ];
        if ($model !== null && trim($model) !== '') {
            $options['model'] = trim($model);
        }

        try {
            $result = $this->ai->complete($prompt, $options);
            if (($result['provider'] ?? '') === 'null') {
                throw new RuntimeException('AI provider is null. Set AI_PROVIDER=ollama and pull a model first.');
            }

            $rawContent = (string) ($result['content'] ?? $result['message'] ?? '');
            $generatedSql = $this->extractSql($rawContent);
            $guarded = $this->sqlGuard->protect($generatedSql, $this->schemaInspector->tableNames());

            $rows = $this->normalizeRows($this->db->raw($guarded['sql']));
            $summary = $this->summarizeRows($rows);

            $payload = [
                'question' => $question,
                'sql' => $guarded['sql'],
                'limit' => $guarded['limit'],
                'row_count' => count($rows),
                'rows' => $rows,
                'summary' => $summary,
                'provider' => $result['provider'] ?? 'unknown',
                'model' => $result['model'] ?? null,
            ];

            $this->traceLogger->info('ai.sql.success', [
                'question' => $question,
                'provider' => $payload['provider'],
                'model' => $payload['model'],
                'sql' => $payload['sql'],
                'row_count' => $payload['row_count'],
                'duration_ms' => round((microtime(true) - $startedAt) * 1000, 2),
            ]);

            return $payload;
        } catch (\Throwable $e) {
            $this->traceLogger->error('ai.sql.failure', [
                'question' => $question,
                'model' => $model,
                'duration_ms' => round((microtime(true) - $startedAt) * 1000, 2),
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * @param array<int, array{name:string, columns:list<string>}> $schema
     */
    private function buildPrompt(string $question, array $schema): string
    {
        $schemaLines = array_map(
            static fn (array $table): string => '- ' . $table['name'] . '(' . implode(', ', $table['columns']) . ')',
            $schema
        );

        return implode("\n", [
            'Database schema:',
            ...$schemaLines,
            '',
            'Rules:',
            '1) Return only one SQL query.',
            '2) Query must start with SELECT.',
            '3) Use only existing tables and columns.',
            '4) Include LIMIT if possible.',
            '5) For aggregate fields, always use explicit aliases (example: COUNT(*) AS total).',
            '',
            'Question:',
            $question,
        ]);
    }

    private function extractSql(string $content): string
    {
        $trimmed = trim($content);
        if ($trimmed === '') {
            throw new RuntimeException('AI response is empty.');
        }

        if (preg_match('/```sql\s*(.*?)```/is', $trimmed, $match)) {
            return trim((string) ($match[1] ?? ''));
        }

        if (preg_match('/```(.*?)```/is', $trimmed, $match)) {
            return trim((string) ($match[1] ?? ''));
        }

        return $trimmed;
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     */
    private function summarizeRows(array $rows): string
    {
        if ($rows === []) {
            return 'Sorgu calisti, sonuc bulunamadi.';
        }

        $firstRow = $rows[0];
        if (array_key_exists('total', $firstRow)) {
            return 'Toplam: ' . (string) $firstRow['total'];
        }

        foreach ($firstRow as $key => $value) {
            $lowerKey = strtolower((string) $key);
            if (
                $lowerKey === 'count'
                || preg_match('/^count\s*\(.*\)$/i', (string) $key) === 1
                || str_ends_with($lowerKey, '_count')
                || str_starts_with($lowerKey, 'count_')
                || str_starts_with($lowerKey, 'total_')
            ) {
                return $this->humanizeAlias((string) $key) . ': ' . (is_scalar($value) ? (string) $value : '[complex]');
            }
        }

        $previewParts = [];
        foreach (array_slice($firstRow, 0, 3, true) as $key => $value) {
            $previewParts[] = $key . '=' . (is_scalar($value) ? (string) $value : '[complex]');
        }

        return sprintf(
            'Toplam %d satir bulundu. Ilk kayit: %s',
            count($rows),
            implode(', ', $previewParts)
        );
    }

    private function humanizeAlias(string $alias): string
    {
        $label = str_replace(['_', '-'], ' ', trim($alias));
        $label = preg_replace('/\s+/', ' ', (string) $label);
        $label = (string) $label;

        return ucfirst($label);
    }

    /**
     * @param array<int, mixed> $rows
     * @return array<int, array<string, mixed>>
     */
    private function normalizeRows(array $rows): array
    {
        return array_map(static function (mixed $row): array {
            if (is_array($row)) {
                return $row;
            }

            if (is_object($row)) {
                /** @var array<string, mixed> $data */
                $data = get_object_vars($row);
                return $data;
            }

            return ['value' => $row];
        }, $rows);
    }
}
