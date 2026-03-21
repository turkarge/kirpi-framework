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
        $this->assertStringContainsString('/manifest.webmanifest', $response->getContent());
        $this->assertStringContainsString("navigator.serviceWorker.register('/sw.js')", $response->getContent());
        $this->assertStringContainsString('window.kirpiNotify', $response->getContent());
    }

    public function test_admin_demo_page_is_accessible(): void
    {
        $response = $this->get('/kirpi/admin-demo');

        $this->assertResponseStatus($response, 200);
        $this->assertStringContainsString('Kirpi Admin Demo - Tabler Layout Fluid', $response->getContent());
        $this->assertStringContainsString('href="/kirpi/admin-demo?theme=dark"', $response->getContent());
        $this->assertStringContainsString('href="/vendor/tabler/dist/css/tabler.css', $response->getContent());
        $this->assertStringContainsString('class="navbar navbar-expand-md d-print-none"', $response->getContent());
        $this->assertStringContainsString('Core Control Center', $response->getContent());
        $this->assertStringContainsString('Kirpi Core Checklist', $response->getContent());
        $this->assertStringContainsString('aria-label="Show notifications"', $response->getContent());
        $this->assertStringContainsString('aria-label="Open user menu"', $response->getContent());
        $this->assertStringNotContainsString('Theme Builder', $response->getContent());
        $this->assertStringContainsString('Copyright &copy; 2026', $response->getContent());
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

    public function test_pwa_test_page_is_accessible_and_contains_runtime_controls(): void
    {
        $response = $this->get('/kirpi/pwa-test');

        $this->assertResponseStatus($response, 200);
        $this->assertStringContainsString('Kirpi PWA Test', $response->getContent());
        $this->assertStringContainsString('PWA Durum Kontrolu', $response->getContent());
        $this->assertStringContainsString('data-kirpi-pwa-install', $response->getContent());
        $this->assertStringContainsString('/manifest.webmanifest', $response->getContent());
        $this->assertStringContainsString("navigator.serviceWorker.register('/sw.js')", $response->getContent());
    }
}
