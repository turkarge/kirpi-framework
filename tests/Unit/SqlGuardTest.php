<?php

declare(strict_types=1);

namespace Tests\Unit;

use Core\AI\Sql\SqlGuard;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class SqlGuardTest extends TestCase
{
    public function test_it_appends_default_limit_for_select_query(): void
    {
        $guard = new SqlGuard([
            'default_limit' => 50,
            'max_rows' => 200,
            'allow_tables' => '*',
        ]);

        $result = $guard->protect('SELECT * FROM users');

        $this->assertSame('SELECT * FROM users LIMIT 50', $result['sql']);
        $this->assertSame(50, $result['limit']);
    }

    public function test_it_clamps_limit_when_requested_limit_is_too_high(): void
    {
        $guard = new SqlGuard([
            'default_limit' => 50,
            'max_rows' => 120,
            'allow_tables' => '*',
        ]);

        $result = $guard->protect('SELECT * FROM users LIMIT 1000');

        $this->assertSame('SELECT * FROM users LIMIT 120', $result['sql']);
        $this->assertSame(120, $result['limit']);
    }

    public function test_it_blocks_non_select_queries(): void
    {
        $guard = new SqlGuard([
            'default_limit' => 50,
            'max_rows' => 200,
            'allow_tables' => '*',
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Only SELECT queries are allowed.');

        $guard->protect('DELETE FROM users');
    }

    public function test_it_blocks_not_allowed_table(): void
    {
        $guard = new SqlGuard([
            'default_limit' => 50,
            'max_rows' => 200,
            'allow_tables' => 'notifications',
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Table is not allowed');

        $guard->protect('SELECT * FROM users');
    }

    public function test_it_blocks_table_not_found_in_schema_snapshot(): void
    {
        $guard = new SqlGuard([
            'default_limit' => 50,
            'max_rows' => 200,
            'allow_tables' => '*',
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Table not found in known schema');

        $guard->protect('SELECT * FROM ghost_table', ['users', 'notifications']);
    }

    public function test_it_normalizes_count_alias_to_total_when_missing(): void
    {
        $guard = new SqlGuard([
            'default_limit' => 50,
            'max_rows' => 200,
            'allow_tables' => '*',
        ]);

        $result = $guard->protect('SELECT COUNT(*) FROM users');

        $this->assertStringContainsString('COUNT(*) AS total', $result['sql']);
        $this->assertSame(1, $result['limit']);
    }

    public function test_it_does_not_append_default_limit_for_count_queries(): void
    {
        $guard = new SqlGuard([
            'default_limit' => 50,
            'max_rows' => 200,
            'allow_tables' => '*',
        ]);

        $result = $guard->protect('SELECT COUNT(id) AS user_count FROM users');

        $this->assertSame('SELECT COUNT(id) AS user_count FROM users', $result['sql']);
        $this->assertSame(1, $result['limit']);
    }
}
