<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\Support\TestCase;

class FrontendUiKitTest extends TestCase
{
    public function test_ui_kit_page_is_accessible(): void
    {
        $response = $this->get('/kirpi/ui-kit');

        $this->assertResponseStatus($response, 200);
        $this->assertStringContainsString('Kirpi Admin UI Kit', $response->getContent());
        $this->assertStringContainsString('Button', $response->getContent());
        $this->assertStringContainsString('Card', $response->getContent());
        $this->assertStringContainsString('Form', $response->getContent());
        $this->assertStringContainsString('Table', $response->getContent());
    }
}
