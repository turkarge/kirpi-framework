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
    }
}