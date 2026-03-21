<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\Support\TestCase;
use Core\Database\QueryBuilder;
use Core\Database\DatabaseManager;

class QueryBuilderTest extends TestCase
{
    private DatabaseManager $db;

    protected function setUp(): void
    {
        parent::setUp();
        $this->db = $this->app->make(DatabaseManager::class);
    }

    public function test_select_generates_correct_sql(): void
{
    $sql = $this->db->table('users')
        ->select('id', 'name', 'email')
        ->toSql();

    $this->assertStringContainsString('SELECT', $sql);
    $this->assertStringContainsString('users', $sql);
    $this->assertStringContainsString('id', $sql);
}

    public function test_where_generates_correct_sql(): void
    {
        $sql = $this->db->table('users')
            ->where('status', 'active')
            ->toSql();

        $this->assertStringContainsString('WHERE', $sql);
        $this->assertStringContainsString('`status`', $sql);
    }

    public function test_multiple_where_clauses(): void
    {
        $sql = $this->db->table('users')
            ->where('status', 'active')
            ->where('is_active', 1)
            ->toSql();

        $this->assertStringContainsString('AND', $sql);
    }

    public function test_or_where(): void
    {
        $sql = $this->db->table('users')
            ->where('role', 'admin')
            ->orWhere('role', 'editor')
            ->toSql();

        $this->assertStringContainsString('OR', $sql);
    }

    public function test_order_by(): void
    {
        $sql = $this->db->table('users')
            ->orderBy('created_at', 'DESC')
            ->toSql();

        $this->assertStringContainsString('ORDER BY', $sql);
        $this->assertStringContainsString('DESC', $sql);
    }

    public function test_limit_and_offset(): void
    {
        $sql = $this->db->table('users')
            ->limit(10)
            ->offset(20)
            ->toSql();

        $this->assertStringContainsString('LIMIT 10', $sql);
        $this->assertStringContainsString('OFFSET 20', $sql);
    }

    public function test_where_in(): void
    {
        $sql = $this->db->table('users')
            ->whereIn('id', [1, 2, 3])
            ->toSql();

        $this->assertStringContainsString('IN', $sql);
    }

    public function test_where_null(): void
    {
        $sql = $this->db->table('users')
            ->whereNull('deleted_at')
            ->toSql();

        $this->assertStringContainsString('IS NULL', $sql);
    }

    public function test_join(): void
    {
        $sql = $this->db->table('users')
            ->join('roles', 'roles.id', '=', 'users.role_id')
            ->toSql();

        $this->assertStringContainsString('JOIN', $sql);
        $this->assertStringContainsString('roles', $sql);
    }

    public function test_insert_and_find(): void
    {
        $id = $this->db->table('users')->insert([
            'name'       => 'Test User',
            'email'      => 'test@kirpi.dev',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertGreaterThan(0, $id);

        $user = $this->db->table('users')->find($id);
        $this->assertEquals('Test User', $user->name);
    }

    public function test_update(): void
    {
        $id = $this->db->table('users')->insert([
            'name'       => 'Old Name',
            'email'      => 'old@kirpi.dev',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $affected = $this->db->table('users')
            ->where('id', $id)
            ->update(['name' => 'New Name']);

        $this->assertEquals(1, $affected);

        $user = $this->db->table('users')->find($id);
        $this->assertEquals('New Name', $user->name);
    }

    public function test_delete(): void
    {
        $id = $this->db->table('users')->insert([
            'name'       => 'Delete Me',
            'email'      => 'delete@kirpi.dev',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $affected = $this->db->table('users')
            ->where('id', $id)
            ->delete();

        $this->assertEquals(1, $affected);
        $this->assertNull($this->db->table('users')->find($id));
    }

    public function test_count(): void
    {
        $this->db->table('users')->insert([
            'name' => 'User 1', 'email' => 'u1@kirpi.dev',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->db->table('users')->insert([
            'name' => 'User 2', 'email' => 'u2@kirpi.dev',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $count = $this->db->table('users')->count();
        $this->assertEquals(2, $count);
    }
}