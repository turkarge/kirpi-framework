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
        $this->assertStringContainsString('Notification', $response->getContent());
        $this->assertStringContainsString('window.kirpiNotify', $response->getContent());
    }

    public function test_admin_demo_page_is_accessible(): void
    {
        $response = $this->get('/kirpi/admin-demo');

        $this->assertResponseStatus($response, 200);
        $this->assertStringContainsString('Kirpi Admin Demo', $response->getContent());
        $this->assertStringContainsString('Navigation', $response->getContent());
        $this->assertStringContainsString('Admin Genel Bakis', $response->getContent());
        $this->assertStringContainsString('Hizli Form', $response->getContent());
        $this->assertStringContainsString('Son Kayitlar', $response->getContent());
    }

    public function test_notify_test_page_is_accessible_and_renders_flash_payload(): void
    {
        $response = $this->get('/kirpi/notify-test?kind=success');

        $this->assertResponseStatus($response, 200);
        $this->assertStringContainsString('Kirpi Notify Test', $response->getContent());
        $this->assertStringContainsString('Backend Flash -> Toast Testi', $response->getContent());
        $this->assertStringContainsString('Flash mesaji olustu: success', $response->getContent());
    }
}
