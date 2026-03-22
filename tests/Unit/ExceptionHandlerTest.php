<?php

declare(strict_types=1);

namespace Tests\Unit;

use Core\Exception\Handler;
use Core\Exception\HttpException;
use Core\Http\Request;
use Core\Logging\Logger;
use PHPUnit\Framework\TestCase;

class ExceptionHandlerTest extends TestCase
{
    public function test_json_error_response_contains_request_id_and_header(): void
    {
        $handler = new Handler(new Logger(storage_path('logs'), 'test-exception'), false);
        $request = new Request(server: [
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/boom',
            'HTTP_ACCEPT' => 'application/json',
        ]);

        $response = $handler->handle(new \RuntimeException('boom'), $request);
        $payload = json_decode($response->getContent(), true);

        $this->assertSame(500, $response->getStatus());
        $this->assertIsArray($payload);
        $this->assertArrayHasKey('request_id', $payload);
        $this->assertNotEmpty($payload['request_id']);
        $this->assertSame($payload['request_id'], $response->getHeaders()['X-Request-Id'] ?? null);
    }

    public function test_http_exception_uses_incoming_request_id_header(): void
    {
        $handler = new Handler(new Logger(storage_path('logs'), 'test-exception'), false);
        $request = new Request(server: [
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/missing',
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_REQUEST_ID' => 'rid-test-123',
        ]);

        $response = $handler->handle(new HttpException(404, 'Not found'), $request);
        $payload = json_decode($response->getContent(), true);

        $this->assertSame(404, $response->getStatus());
        $this->assertSame('rid-test-123', $payload['request_id'] ?? null);
        $this->assertSame('rid-test-123', $response->getHeaders()['X-Request-Id'] ?? null);
    }
}

