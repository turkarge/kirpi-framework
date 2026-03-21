<?php

declare(strict_types=1);

namespace Tests\Unit;

use Core\Frontend\ViewModels\AdminDemoViewModel;
use PHPUnit\Framework\TestCase;

class AdminDemoViewModelTest extends TestCase
{
    public function test_it_returns_expected_demo_payload_shape(): void
    {
        $payload = (new AdminDemoViewModel())->toArray();

        $this->assertSame('Kirpi Admin Demo', $payload['title']);
        $this->assertSame('Kirpi Admin Demo', $payload['heroTitle']);
        $this->assertIsArray($payload['cards']);
        $this->assertCount(2, $payload['cards']);
        $this->assertSame('Toplam Teklif', $payload['cards'][0]['title']);
        $this->assertSame('Yeni Teklif', $payload['actions']['save']);
    }
}
