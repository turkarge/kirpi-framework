<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\Support\TestCase;

class BrowserPreviewTest extends TestCase
{
    public function test_kirpi_preview_page_is_accessible(): void
    {
        $response = $this->get('/kirpi');

        $this->assertResponseStatus($response, 200);
        $this->assertStringContainsString('Kirpi Runtime', $response->getContent());
        $this->assertStringContainsString('Run Self-Check', $response->getContent());
        $this->assertStringContainsString('DB:', $response->getContent());
        $this->assertStringContainsString('Cache:', $response->getContent());
    }

    public function test_kirpi_self_check_endpoint_returns_runtime_checks(): void
    {
        $response = $this->get('/kirpi/self-check');

        $this->assertResponseStatus($response, 200);

        $data = json_decode($response->getContent(), true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('status', $data);
        $this->assertArrayHasKey('checks', $data);
        $this->assertArrayHasKey('database', $data['checks']);
        $this->assertArrayHasKey('cache', $data['checks']);
    }

    public function test_kirpi_self_check_history_endpoint_returns_items(): void
    {
        $this->get('/kirpi/self-check');
        $this->get('/kirpi/self-check');

        $response = $this->get('/kirpi/self-check/history');

        $this->assertResponseStatus($response, 200);

        $data = json_decode($response->getContent(), true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('items', $data);
        $this->assertIsArray($data['items']);
        $this->assertNotEmpty($data['items']);
        $this->assertArrayHasKey('status', $data['items'][0]);
        $this->assertArrayHasKey('checks', $data['items'][0]);
    }

    public function test_ready_endpoint_returns_operational_payload(): void
    {
        $response = $this->get('/ready');
        $this->assertContains($response->getStatus(), [200, 503]);

        $data = json_decode($response->getContent(), true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('status', $data);
        $this->assertArrayHasKey('checks', $data);
        $this->assertArrayHasKey('database', $data['checks']);
        $this->assertArrayHasKey('cache', $data['checks']);
    }
}
