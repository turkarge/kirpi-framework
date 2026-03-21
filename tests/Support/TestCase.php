<?php

declare(strict_types=1);

namespace Tests\Support;

use Core\Container\Container;
use Core\Database\DatabaseManager;
use Core\Http\Request;
use Core\Http\Response;
use Core\Migration\Migrator;
use Core\Routing\Router;
use Modules\Users\Models\User;
use PHPUnit\Framework\TestCase as BaseTestCase;
use ReflectionClass;

abstract class TestCase extends BaseTestCase
{
    protected Container $app;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app = Container::getInstance();

        $router = $this->app->make(Router::class);
        $router->loadRoutes(BASE_PATH . '/routes/web.php', ['middleware' => 'web']);
        $router->loadRoutes(BASE_PATH . '/routes/api.php', ['middleware' => 'api']);

        $this->runMigrations();
    }

    protected function tearDown(): void
    {
        $this->resetDatabase();

        parent::tearDown();
    }

    protected function runMigrations(): void
    {
        $migrator = $this->app->make(Migrator::class);
        $migrator->run();
    }

    protected function resetDatabase(): void
    {
        try {
            $db = $this->app->make(DatabaseManager::class);
            $db->raw('DELETE FROM users');
            $db->raw('DELETE FROM notifications');
            $db->raw("DELETE FROM sqlite_sequence WHERE name='users'");
        } catch (\Throwable) {
            // Intentionally ignored for tests that do not initialize database tables.
        }
    }

    protected function get(string $uri, array $headers = []): Response
    {
        return $this->request('GET', $uri, [], $headers);
    }

    protected function post(string $uri, array $data = [], array $headers = []): Response
    {
        return $this->request('POST', $uri, $data, $headers);
    }

    protected function put(string $uri, array $data = [], array $headers = []): Response
    {
        return $this->request('PUT', $uri, $data, $headers);
    }

    protected function delete(string $uri, array $headers = []): Response
    {
        return $this->request('DELETE', $uri, [], $headers);
    }

    protected function request(string $method, string $uri, array $data = [], array $headers = []): Response
    {
        $server = [
            'REQUEST_METHOD' => $method,
            'REQUEST_URI' => $uri,
            'HTTP_HOST' => 'localhost',
            'CONTENT_TYPE' => 'application/json',
        ];

        foreach ($headers as $key => $value) {
            $server['HTTP_' . strtoupper(str_replace('-', '_', $key))] = $value;
        }

        if ($data !== []) {
            $server['CONTENT_TYPE'] = 'application/json';
        }

        $request = new Request(
            query: [],
            post: [],
            files: [],
            server: $server,
            cookies: [],
        );

        if ($data !== []) {
            $reflection = new ReflectionClass($request);
            $property = $reflection->getProperty('jsonBody');
            $property->setValue($request, $data);
        }

        $router = $this->app->make(Router::class);

        return $router->dispatch($request);
    }

    protected function actingAs(User $user, string $guard = 'session'): static
    {
        \Core\Auth\Facades\Auth::guard($guard)->login($user);

        return $this;
    }

    protected function assertResponseOk(Response $response): void
    {
        $this->assertEquals(200, $response->getStatus());
    }

    protected function assertResponseStatus(Response $response, int $status): void
    {
        $this->assertEquals($status, $response->getStatus());
    }

    protected function assertJsonResponse(Response $response, array $expected): void
    {
        $actual = json_decode($response->getContent(), true);

        foreach ($expected as $key => $value) {
            $this->assertArrayHasKey($key, $actual);
            $this->assertEquals($value, $actual[$key]);
        }
    }

    protected function assertDatabaseHas(string $table, array $conditions): void
    {
        $db = $this->app->make(DatabaseManager::class);
        $query = $db->table($table);

        foreach ($conditions as $column => $value) {
            $query->where($column, $value);
        }

        $this->assertTrue($query->exists(), "Failed asserting that table [{$table}] has record.");
    }

    protected function assertDatabaseMissing(string $table, array $conditions): void
    {
        $db = $this->app->make(DatabaseManager::class);
        $query = $db->table($table);

        foreach ($conditions as $column => $value) {
            $query->where($column, $value);
        }

        $this->assertFalse($query->exists(), "Failed asserting that table [{$table}] does not have record.");
    }

    protected function assertDatabaseCount(string $table, int $count): void
    {
        $db = $this->app->make(DatabaseManager::class);
        $actual = $db->table($table)->count();

        $this->assertEquals($count, $actual, "Failed asserting that table [{$table}] has [{$count}] records.");
    }
}