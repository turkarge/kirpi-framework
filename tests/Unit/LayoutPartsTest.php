<?php

declare(strict_types=1);

namespace Tests\Unit;

use Core\Frontend\Tabler\LayoutParts;
use PHPUnit\Framework\TestCase;

class LayoutPartsTest extends TestCase
{
    public function test_page_header_renders_expected_structure_and_escapes_content(): void
    {
        $parts = new LayoutParts();
        $header = $parts->pageHeader('Kirpi <Admin>', 'Core "UI" subtitle');

        $this->assertStringContainsString('BEGIN PAGE HEADER', $header);
        $this->assertStringContainsString('Kirpi Framework', $header);
        $this->assertStringContainsString('Kirpi &lt;Admin&gt;', $header);
        $this->assertStringContainsString('Core &quot;UI&quot; subtitle', $header);
    }

    public function test_page_body_wraps_given_content(): void
    {
        $parts = new LayoutParts();
        $body = $parts->pageBody('<section id="x">ok</section>');

        $this->assertStringContainsString('BEGIN PAGE BODY', $body);
        $this->assertStringContainsString('<section id="x">ok</section>', $body);
        $this->assertStringContainsString('container-xl', $body);
    }

    public function test_footer_contains_kirpi_branding(): void
    {
        $parts = new LayoutParts();
        $footer = $parts->footer();

        $this->assertStringContainsString('Kirpi Framework', $footer);
        $this->assertStringContainsString('Copyright &copy; 2026', $footer);
        $this->assertStringContainsString('/kirpi/admin-demo', $footer);
    }
}

