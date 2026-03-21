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
        $this->assertStringContainsString('/assets/admin.css', $response->getContent());
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
        $this->assertStringContainsString('Kirpi Admin Demo - Tabler Layout Fluid', $response->getContent());
        $this->assertStringContainsString('<base href="/vendor/tabler/">', $response->getContent());
        $this->assertStringContainsString('Fluid layout', $response->getContent());
        $this->assertStringContainsString('Dashboard', $response->getContent());
    }

    public function test_notify_test_page_is_accessible_and_renders_flash_payload(): void
    {
        $response = $this->get('/kirpi/notify-test?kind=success');

        $this->assertResponseStatus($response, 200);
        $this->assertStringContainsString('Kirpi Notify Test', $response->getContent());
        $this->assertStringContainsString('Backend Flash -> Toast Testi', $response->getContent());
        $this->assertStringContainsString('Flash mesaji olustu: success', $response->getContent());
    }

    public function test_api_notify_test_page_is_accessible_and_has_api_bridge(): void
    {
        $response = $this->get('/kirpi/api-notify-test');

        $this->assertResponseStatus($response, 200);
        $this->assertStringContainsString('Kirpi API Notify Test', $response->getContent());
        $this->assertStringContainsString('API Response -> Notify Testi', $response->getContent());
        $this->assertStringContainsString('window.kirpiApi', $response->getContent());
        $this->assertStringContainsString('/kirpi/api-notify-sample?case=', $response->getContent());
    }

    public function test_api_notify_sample_endpoint_returns_expected_payload_shapes(): void
    {
        $success = $this->get('/kirpi/api-notify-sample?case=success');
        $error = $this->get('/kirpi/api-notify-sample?case=error');

        $this->assertResponseStatus($success, 200);
        $this->assertStringContainsString('Kayit basariyla olusturuldu.', $success->getContent());

        $this->assertResponseStatus($error, 500);
        $this->assertStringContainsString('Servis gecici olarak kullanilamiyor.', $error->getContent());
    }
}
