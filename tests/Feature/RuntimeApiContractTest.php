<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\Support\TestCase;

class RuntimeApiContractTest extends TestCase
{
    public function test_self_check_endpoint_contract(): void
    {
        $response = $this->get('/kirpi/self-check');

        $this->assertResponseStatus($response, 200);

        $data = json_decode($response->getContent(), true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('status', $data);
        $this->assertArrayHasKey('checks', $data);
        $this->assertArrayHasKey('latency_trend', $data);
        $this->assertArrayHasKey('database', $data['checks']);
        $this->assertArrayHasKey('cache', $data['checks']);
        $this->assertArrayHasKey('points', $data['latency_trend']);
    }

    public function test_self_check_history_endpoint_contract(): void
    {
        $this->get('/kirpi/self-check');
        $this->get('/kirpi/self-check');

        $response = $this->get('/kirpi/self-check/history');

        $this->assertResponseStatus($response, 200);

        $data = json_decode($response->getContent(), true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('items', $data);
        $this->assertArrayHasKey('latency_trend', $data);
        $this->assertIsArray($data['items']);
        $this->assertNotEmpty($data['items']);
        $this->assertArrayHasKey('status', $data['items'][0]);
        $this->assertArrayHasKey('checks', $data['items'][0]);
        $this->assertArrayHasKey('points', $data['latency_trend']);
    }

    public function test_ready_endpoint_contract(): void
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
