<?php

declare(strict_types=1);

namespace Core\Frontend\Tabler;

final class LayoutTransformer
{
    public function normalizeTablerPaths(string $html): string
    {
        $html = str_replace('href="./dist/', 'href="/vendor/tabler/dist/', $html);
        $html = str_replace('src="./dist/', 'src="/vendor/tabler/dist/', $html);
        $html = str_replace('href="./preview/', 'href="/vendor/tabler/preview/', $html);
        $html = str_replace('src="./preview/', 'src="/vendor/tabler/preview/', $html);
        $html = str_replace('href="./static/', 'href="/vendor/tabler/static/', $html);
        $html = str_replace('src="./static/', 'src="/vendor/tabler/static/', $html);
        $html = str_replace('href="./favicon.ico"', 'href="/vendor/tabler/favicon.ico"', $html);
        $html = str_replace('href="."', 'href="/kirpi/admin-demo"', $html);
        $html = str_replace('href="?theme=dark"', 'href="/kirpi/admin-demo?theme=dark"', $html);
        $html = str_replace('href="?theme=light"', 'href="/kirpi/admin-demo?theme=light"', $html);

        return $html;
    }

    public function applyTablerShellPatches(string $html, string $currentPath): string
    {
        $html = $this->replaceBetweenMarkers(
            $html,
            '<!-- BEGIN NAVBAR MENU -->',
            '<!-- END NAVBAR MENU -->',
            $this->kirpiNavbarMenu($currentPath)
        );

        $html = (string) preg_replace('/<a[^>]*aria-label="Show app menu"[^>]*>.*?<\/a>/si', '', $html);
        $html = (string) preg_replace('/<a[^>]*>\s*Source\s*code\s*<\/a>/i', '', $html);
        $html = (string) preg_replace('/<a[^>]*>\s*Sponsor(?:\s*project!?)?\s*<\/a>/i', '', $html);

        return $html;
    }

    public function stripThemeBuilderAndModals(string $html): string
    {
        $html = (string) preg_replace('/<!-- BEGIN PAGE MODALS -->.*?<!-- END PAGE MODALS -->/s', '', $html);

        return (string) preg_replace('/<div class="settings">.*?<\/form>\s*<\/div>/s', '', $html);
    }

    private function kirpiNavbarMenu(string $currentPath): string
    {
        $links = [
            '/kirpi/admin-demo' => 'Dashboard',
            '/kirpi/ui-kit' => 'UI Kit',
            '/kirpi/notify-test' => 'Notify Test',
            '/kirpi/api-notify-test' => 'API Notify Test',
        ];

        $items = [];
        foreach ($links as $path => $label) {
            $activeClass = $path === $currentPath ? ' active' : '';
            $safeLabel = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
            $safePath = htmlspecialchars($path, ENT_QUOTES, 'UTF-8');
            $items[] = "            <li class=\"nav-item{$activeClass}\"><a class=\"nav-link\" href=\"{$safePath}\"><span class=\"nav-link-title\"> {$safeLabel} </span></a></li>";
        }

        return "<!-- BEGIN NAVBAR MENU -->\n          <ul class=\"navbar-nav\">\n" . implode("\n", $items) . "\n          </ul>\n          <!-- END NAVBAR MENU -->";
    }

    private function replaceBetweenMarkers(string $html, string $startMarker, string $endMarker, string $replacement): string
    {
        $start = strpos($html, $startMarker);
        $end = strpos($html, $endMarker);
        if ($start === false || $end === false || $end < $start) {
            return $html;
        }

        $end += strlen($endMarker);

        return substr($html, 0, $start) . $replacement . substr($html, $end);
    }
}
