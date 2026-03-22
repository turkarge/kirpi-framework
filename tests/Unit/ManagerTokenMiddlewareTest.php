<?php

declare(strict_types=1);

namespace Tests\Unit;

use Core\Http\Request;
use Core\Http\Response;
use Manager\Http\Middleware\RequireManagerToken;
use PHPUnit\Framework\TestCase;

class ManagerTokenMiddlewareTest extends TestCase
{
    protected function tearDown(): void
    {
        unset($_ENV['KIRPI_MANAGER_TOKEN'], $_SERVER['KIRPI_MANAGER_TOKEN']);
        putenv('KIRPI_MANAGER_TOKEN');
        parent::tearDown();
    }

    public function test_it_blocks_when_token_missing_or_invalid(): void
    {
        $_ENV['KIRPI_MANAGER_TOKEN'] = 'secret-token';
        $_SERVER['KIRPI_MANAGER_TOKEN'] = 'secret-token';
        putenv('KIRPI_MANAGER_TOKEN=secret-token');

        $middleware = new RequireManagerToken();
        $request = new Request(server: [
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/manager/api/overview',
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_MANAGER_TOKEN' => 'invalid',
        ]);

        $response = $middleware->handle($request, fn () => Response::json(['ok' => true]));

        $this->assertSame(401, $response->getStatus());
    }

    public function test_it_allows_when_token_matches(): void
    {
        $_ENV['KIRPI_MANAGER_TOKEN'] = 'secret-token';
        $_SERVER['KIRPI_MANAGER_TOKEN'] = 'secret-token';
        putenv('KIRPI_MANAGER_TOKEN=secret-token');

        $middleware = new RequireManagerToken();
        $request = new Request(server: [
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/manager/api/overview',
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_MANAGER_TOKEN' => 'secret-token',
        ]);

        $response = $middleware->handle($request, fn () => Response::json(['ok' => true]));

        $this->assertSame(200, $response->getStatus());
    }
}

