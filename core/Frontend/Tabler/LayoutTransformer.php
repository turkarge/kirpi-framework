<?php

declare(strict_types=1);

namespace Core\Frontend\Tabler;

final class LayoutTransformer
{
    public function normalizeTablerPaths(string $html): string
    {
        $html = str_replace('href="./dist/', 'href="/vendor/tabler/dist/', $html);
        $html = str_replace('src="./dist/', 'src="/vendor/tabler/dist/', $html);
        $html = str_replace('href="."', 'href="/kirpi/admin-demo"', $html);

        return $html;
    }

    public function applyTablerShellPatches(string $html, string $currentPath): string
    {
        $html = str_replace('href="?theme=dark"', 'href="' . $currentPath . '?theme=dark"', $html);
        $html = str_replace('href="?theme=light"', 'href="' . $currentPath . '?theme=light"', $html);
        $html = $this->replaceBetweenMarkers(
            $html,
            '<!-- BEGIN PAGE LEVEL STYLES -->',
            '<!-- END PAGE LEVEL STYLES -->',
            "  <!-- BEGIN PAGE LEVEL STYLES -->\n  <!-- END PAGE LEVEL STYLES -->"
        );
        $html = $this->replaceBetweenMarkers(
            $html,
            '<!-- BEGIN PAGE LIBRARIES -->',
            '<!-- END PAGE LIBRARIES -->',
            "  <!-- BEGIN PAGE LIBRARIES -->\n  <!-- END PAGE LIBRARIES -->"
        );
        $html = $this->replaceBetweenMarkers(
            $html,
            '<!-- BEGIN DEMO SCRIPTS -->',
            '<!-- END DEMO SCRIPTS -->',
            "  <!-- BEGIN DEMO SCRIPTS -->\n  <!-- END DEMO SCRIPTS -->"
        );

        $html = $this->replaceBetweenMarkers(
            $html,
            '<!-- BEGIN NAVBAR MENU -->',
            '<!-- END NAVBAR MENU -->',
            $this->kirpiNavbarMenu($currentPath)
        );
        $html = $this->replaceDropdownByAriaLabel(
            $html,
            ariaLabel: 'Show notifications',
            wrapperClass: 'nav-item dropdown d-none d-md-flex',
            replacement: $this->kirpiNavbarNotifications()
        );
        $html = $this->replaceDropdownByAriaLabel(
            $html,
            ariaLabel: 'Open user menu',
            wrapperClass: 'nav-item dropdown',
            replacement: $this->kirpiNavbarUserMenu()
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

    private function kirpiNavbarNotifications(): string
    {
        return <<<'HTML'
            <div class="nav-item dropdown d-none d-md-flex">
              <a href="#" class="nav-link px-0" data-bs-toggle="dropdown" tabindex="-1" aria-label="Show notifications" data-bs-auto-close="outside" aria-expanded="false">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-1">
                  <path d="M10 5a2 2 0 1 1 4 0a7 7 0 0 1 4 6v3a4 4 0 0 0 2 3h-16a4 4 0 0 0 2 -3v-3a7 7 0 0 1 4 -6" />
                  <path d="M9 17v1a3 3 0 0 0 6 0v-1" />
                </svg>
                <span class="badge bg-red"></span>
              </a>
              <div class="dropdown-menu dropdown-menu-arrow dropdown-menu-end dropdown-menu-card">
                <div class="card">
                  <div class="card-header d-flex">
                    <h3 class="card-title">Kirpi Notifications</h3>
                    <div class="btn-close ms-auto" data-bs-dismiss="dropdown"></div>
                  </div>
                  <div class="list-group list-group-flush list-group-hoverable">
                    <div class="list-group-item">
                      <div class="row align-items-center">
                        <div class="col-auto"><span class="status-dot status-dot-animated bg-green d-block"></span></div>
                        <div class="col text-truncate">
                          <span class="text-body d-block">Frontend shell standardi aktif</span>
                          <div class="d-block text-secondary text-truncate mt-n1">Tabler + Kirpi patch katmani calisiyor</div>
                        </div>
                      </div>
                    </div>
                    <div class="list-group-item">
                      <div class="row align-items-center">
                        <div class="col-auto"><span class="status-dot d-block"></span></div>
                        <div class="col text-truncate">
                          <span class="text-body d-block">Notify bridge hazir</span>
                          <div class="d-block text-secondary text-truncate mt-n1">Flash ve API response toast akisi hazir</div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="card-body">
                    <div class="row">
                      <div class="col">
                        <a href="/kirpi/notify-test" class="btn btn-1 w-100">Notify Test</a>
                      </div>
                      <div class="col">
                        <a href="/kirpi/api-notify-test" class="btn btn-primary w-100">API Test</a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
HTML;
    }

    private function kirpiNavbarUserMenu(): string
    {
        return <<<'HTML'
            <div class="nav-item dropdown">
              <a href="#" class="nav-link d-flex lh-1 p-0 px-2" data-bs-toggle="dropdown" aria-label="Open user menu">
                <span class="avatar avatar-sm">KF</span>
                <div class="d-none d-xl-block ps-2">
                  <div>Kirpi Admin</div>
                  <div class="mt-1 small text-secondary">owner</div>
                </div>
              </a>
              <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                <a href="/kirpi/admin-demo" class="dropdown-item">Dashboard</a>
                <a href="/kirpi/ui-kit" class="dropdown-item">UI Kit</a>
                <div class="dropdown-divider"></div>
                <a href="/kirpi/notify-test" class="dropdown-item">Notify Test</a>
              </div>
            </div>
HTML;
    }

    private function kirpiNavbarMenu(string $currentPath): string
    {
        $links = [
            '/kirpi/admin-demo' => 'Dashboard',
            '/kirpi/ui-kit' => 'UI Kit',
            '/kirpi/notify-test' => 'Notify Test',
            '/kirpi/api-notify-test' => 'API Notify Test',
            '/kirpi/pwa-test' => 'PWA Test',
            '/kirpi/modal-test' => 'Modal Test',
            '/kirpi/import-export-test' => 'Import/Export Test',
            '/kirpi/state-test' => 'State Test',
            '/kirpi/a11y-test' => 'A11y Test',
        ];

        if ((bool) env('KIRPI_FEATURE_AI', false)) {
            $links['/kirpi/ai-sql-test'] = 'AI SQL Test';
        }

        $items = [];
        $shortcutMap = [
            '/kirpi/admin-demo' => 'Alt+1',
            '/kirpi/ui-kit' => 'Alt+2',
            '/kirpi/notify-test' => 'Alt+3',
            '/kirpi/state-test' => 'Alt+4',
            '/kirpi/a11y-test' => 'Alt+5',
        ];

        foreach ($links as $path => $label) {
            $activeClass = $path === $currentPath ? ' active' : '';
            $safeLabel = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
            $safePath = htmlspecialchars($path, ENT_QUOTES, 'UTF-8');
            $shortcutAttr = isset($shortcutMap[$path])
                ? ' aria-keyshortcuts="' . htmlspecialchars($shortcutMap[$path], ENT_QUOTES, 'UTF-8') . '"'
                : '';
            $items[] = "            <li class=\"nav-item{$activeClass}\"><a class=\"nav-link\" href=\"{$safePath}\"{$shortcutAttr}><span class=\"nav-link-title\"> {$safeLabel} </span></a></li>";
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

    private function replaceDropdownByAriaLabel(string $html, string $ariaLabel, string $wrapperClass, string $replacement): string
    {
        $anchorNeedle = 'aria-label="' . $ariaLabel . '"';
        $anchorPos = strpos($html, $anchorNeedle);
        if ($anchorPos === false) {
            return $html;
        }

        $wrapperNeedle = '<div class="' . $wrapperClass . '">';
        $startPos = strrpos(substr($html, 0, $anchorPos), $wrapperNeedle);
        if ($startPos === false) {
            return $html;
        }

        $endPos = $this->findMatchingDivEnd($html, $startPos);
        if ($endPos === null) {
            return $html;
        }

        return substr($html, 0, $startPos) . $replacement . substr($html, $endPos);
    }

    private function findMatchingDivEnd(string $html, int $startPos): ?int
    {
        $tokenPattern = '/<div\b[^>]*>|<\/div>/i';
        if (!preg_match_all($tokenPattern, $html, $matches, PREG_OFFSET_CAPTURE, $startPos)) {
            return null;
        }

        $depth = 0;
        foreach ($matches[0] as [$token, $offset]) {
            if (str_starts_with(strtolower($token), '<div')) {
                $depth++;
            } else {
                $depth--;
                if ($depth === 0) {
                    return $offset + strlen($token);
                }
            }
        }

        return null;
    }
}
