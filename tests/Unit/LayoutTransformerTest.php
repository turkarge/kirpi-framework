<?php

declare(strict_types=1);

namespace Tests\Unit;

use Core\Frontend\Tabler\LayoutTransformer;
use PHPUnit\Framework\TestCase;

class LayoutTransformerTest extends TestCase
{
    public function test_normalize_tabler_paths_rewrites_local_asset_paths(): void
    {
        $transformer = new LayoutTransformer();
        $html = '<link href="./dist/css/tabler.css"><a href="?theme=dark"></a>';

        $result = $transformer->normalizeTablerPaths($html);

        $this->assertStringContainsString('href="/vendor/tabler/dist/css/tabler.css"', $result);
        $this->assertStringContainsString('href="?theme=dark"', $result);
    }

    public function test_apply_tabler_shell_patches_replaces_nav_menu_and_cleans_optional_links(): void
    {
        $transformer = new LayoutTransformer();
        $html = <<<'HTML'
<div>
  <a aria-label="Show app menu" href="#">menu</a>
  <a href="#">Source code</a>
  <a href="#">Sponsor project!</a>
  <!-- BEGIN NAVBAR MENU -->
  <ul class="navbar-nav"><li class="nav-item active"><a class="nav-link" href="./"><span class="nav-link-title"> Home </span></a></li></ul>
  <!-- END NAVBAR MENU -->
  <a href="?theme=dark">dark</a>
  <a href="?theme=light">light</a>
</div>
HTML;

        $result = $transformer->applyTablerShellPatches($html, '/kirpi/notify-test');

        $this->assertStringNotContainsString('Show app menu', $result);
        $this->assertStringNotContainsString('Source code', $result);
        $this->assertStringNotContainsString('Sponsor project!', $result);
        $this->assertStringContainsString('/kirpi/notify-test', $result);
        $this->assertStringContainsString('nav-item active', $result);
        $this->assertStringContainsString('API Notify Test', $result);
        $this->assertStringContainsString('href="/kirpi/notify-test?theme=dark"', $result);
        $this->assertStringContainsString('href="/kirpi/notify-test?theme=light"', $result);
    }

    public function test_strip_theme_builder_and_modals_removes_unsafe_blocks(): void
    {
        $transformer = new LayoutTransformer();
        $html = <<<'HTML'
<div>
  <!-- BEGIN PAGE MODALS -->
  <div>modal</div>
  <!-- END PAGE MODALS -->
  <div class="settings"><form><input name="x"></form></div>
  <span>keep-me</span>
</div>
HTML;

        $result = $transformer->stripThemeBuilderAndModals($html);

        $this->assertStringNotContainsString('BEGIN PAGE MODALS', $result);
        $this->assertStringNotContainsString('class="settings"', $result);
        $this->assertStringContainsString('keep-me', $result);
    }

    public function test_apply_tabler_shell_patches_replaces_notification_and_user_menu_blocks_safely(): void
    {
        $transformer = new LayoutTransformer();
        $html = <<<'HTML'
<header>
  <div class="navbar-nav flex-row order-md-last">
    <div class="d-none d-md-flex">
      <div class="nav-item dropdown d-none d-md-flex">
        <a href="#" aria-label="Show notifications"></a>
        <div class="dropdown-menu">old notifications</div>
      </div>
    </div>
    <div class="nav-item dropdown">
      <a href="#" aria-label="Open user menu"></a>
      <div class="dropdown-menu">old user menu</div>
    </div>
  </div>
  <!-- BEGIN NAVBAR MENU -->
  <ul class="navbar-nav"><li class="nav-item active">old menu</li></ul>
  <!-- END NAVBAR MENU -->
</header>
HTML;

        $result = $transformer->applyTablerShellPatches($html, '/kirpi/ui-kit');

        $this->assertStringNotContainsString('old notifications', $result);
        $this->assertStringNotContainsString('old user menu', $result);
        $this->assertStringContainsString('Kirpi Notifications', $result);
        $this->assertStringContainsString('Kirpi Admin', $result);
        $this->assertStringContainsString('/kirpi/api-notify-test', $result);
        $this->assertStringContainsString('nav-item active', $result);
    }
}
