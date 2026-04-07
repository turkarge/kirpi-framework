<?php

declare(strict_types=1);

namespace Core\Auth;

final class DashboardShellRenderer
{
    private static ?string $templateCache = null;
    private static ?string $notifyPartialCache = null;

    public function render(
        string $title,
        string $currentPath,
        string $appName,
        string $userName,
        string $userEmail,
        string $headerHtml,
        string $bodyHtml,
        ?string $footerHtml = null
    ): ?string {
        $html = $this->loadTemplate();
        if ($html === null) {
            return null;
        }

        $safeTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        $safeName = htmlspecialchars($userName, ENT_QUOTES, 'UTF-8');
        $safeEmail = htmlspecialchars($userEmail, ENT_QUOTES, 'UTF-8');
        $safeAppName = htmlspecialchars($appName, ENT_QUOTES, 'UTF-8');

        $html = (string) str_replace('./dist/', '/vendor/tabler/dist/', $html);
        $html = (string) preg_replace('/<link[^>]+\.\/preview\/css\/demo\.css[^>]*>\s*/i', '', $html);
        $html = (string) preg_replace('/<script[^>]+\.\/preview\/js\/demo\.min\.js[^>]*>\s*<\/script>\s*/i', '', $html);
        $html = (string) preg_replace('/<link[^>]+jsvectormap\.css[^>]*>\s*/i', '', $html);
        $html = (string) preg_replace('/<title>.*?<\/title>/si', '<title>' . $safeTitle . '</title>', $html, 1);

        $html = str_replace('href="?theme=dark"', 'href="' . $currentPath . '?theme=dark"', $html);
        $html = str_replace('href="?theme=light"', 'href="' . $currentPath . '?theme=light"', $html);
        $html = str_replace('href="./sign-in.html"', 'href="/exit"', $html);

        $html = str_replace('PaweÅ‚ Kuna', $safeName, $html);
        $html = str_replace('PaweÃ…â€š Kuna', $safeName, $html);
        $html = str_replace('PaweÃƒâ€¦Ã¢â‚¬Å¡ Kuna', $safeName, $html);
        $html = str_replace('UI Designer', $safeEmail, $html);
        $html = (string) preg_replace('/style="background-image:\s*url\([^)]*000m\.jpg[^)]*\)"/i', '', $html);
        $html = str_replace('aria-label="Tabler"', 'aria-label="' . $safeAppName . '"', $html);
        $html = str_replace('Copyright &copy; 2025', 'Copyright &copy; ' . date('Y'), $html);
        $html = str_replace('<a href="." class="link-secondary">Tabler</a>', '<a href="/" class="link-secondary">' . $safeAppName . '</a>', $html);
        $html = str_replace('<a href="./license.html" class="link-secondary">License</a>', '<a href="/tos" class="link-secondary">Terms</a>', $html);
        $html = (string) preg_replace('/<a href="https:\/\/github\.com\/sponsors\/codecalm"[\s\S]*?<\/a>/i', '', $html);

        $html = $this->replaceBetweenMarkers($html, '<!-- BEGIN NAVBAR MENU -->', '<!-- END NAVBAR MENU -->', $this->navbarMenuHtml($currentPath));
        $html = $this->replaceBetweenMarkers($html, '<!-- BEGIN PAGE HEADER -->', '<!-- END PAGE HEADER -->', $headerHtml);
        $html = $this->replaceBetweenMarkers($html, '<!-- BEGIN PAGE BODY -->', '<!-- END PAGE BODY -->', $bodyHtml);
        $resolvedFooter = $footerHtml ?? $this->defaultFooterHtml($safeAppName);
        $html = $this->replaceBetweenMarkers($html, '<!--  BEGIN FOOTER  -->', '<!--  END FOOTER  -->', $resolvedFooter);
        $html = $this->appendBeforeBodyEnd($html, $this->renderNotifyPartial());

        return $html;
    }

    private function loadTemplate(): ?string
    {
        if (self::$templateCache !== null) {
            return self::$templateCache;
        }

        $path = BASE_PATH . '/core/Auth/templates/dashboard.html';
        if (!is_file($path)) {
            return null;
        }

        self::$templateCache = (string) file_get_contents($path);

        return self::$templateCache;
    }

    private function navbarMenuHtml(string $currentPath): string
    {
        $dashboard = htmlspecialchars((string) __('auth.web.nav.dashboard'), ENT_QUOTES, 'UTF-8');
        $management = htmlspecialchars((string) __('auth.web.nav.management'), ENT_QUOTES, 'UTF-8');
        $roles = htmlspecialchars((string) __('auth.web.nav.roles'), ENT_QUOTES, 'UTF-8');
        $users = htmlspecialchars((string) __('auth.web.nav.users'), ENT_QUOTES, 'UTF-8');
        $locales = htmlspecialchars((string) __('auth.web.nav.locales'), ENT_QUOTES, 'UTF-8');

        $dashboardActive = $currentPath === '/dashboard' ? ' active' : '';
        $managementActive = in_array($currentPath, ['/roles', '/users', '/locales'], true) ? ' active' : '';
        $rolesItemClass = $currentPath === '/roles' ? 'dropdown-item active' : 'dropdown-item';
        $usersItemClass = $currentPath === '/users' ? 'dropdown-item active' : 'dropdown-item';
        $localesItemClass = $currentPath === '/locales' ? 'dropdown-item active' : 'dropdown-item';
        $rolesAria = $currentPath === '/roles' ? ' aria-current="true"' : '';
        $usersAria = $currentPath === '/users' ? ' aria-current="true"' : '';
        $localesAria = $currentPath === '/locales' ? ' aria-current="true"' : '';

        return <<<HTML
          <!-- BEGIN NAVBAR MENU -->
          <ul class="navbar-nav">
            <li class="nav-item{$dashboardActive}">
              <a class="nav-link" href="/dashboard">
                <span class="nav-link-title"> {$dashboard} </span>
              </a>
            </li>
            <li class="nav-item dropdown{$managementActive}">
              <a class="nav-link dropdown-toggle" href="#navbar-management" data-bs-toggle="dropdown" data-bs-auto-close="outside" role="button" aria-expanded="false">
                <span class="nav-link-title"> {$management} </span>
              </a>
              <div class="dropdown-menu">
                <a class="{$rolesItemClass}" href="/roles"{$rolesAria}>{$roles}</a>
                <a class="{$usersItemClass}" href="/users"{$usersAria}>{$users}</a>
                <a class="{$localesItemClass}" href="/locales"{$localesAria}>{$locales}</a>
              </div>
            </li>
          </ul>
          <!-- END NAVBAR MENU -->
HTML;
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

    private function appendBeforeBodyEnd(string $html, string $content): string
    {
        $needle = '</body>';
        $position = stripos($html, $needle);
        if ($position === false) {
            return $html . $content;
        }

        return substr($html, 0, $position) . $content . PHP_EOL . substr($html, $position);
    }

    private function renderNotifyPartial(): string
    {
        if (self::$notifyPartialCache !== null) {
            return self::$notifyPartialCache;
        }

        $path = BASE_PATH . '/core/Frontend/templates/admin/partials/notify.php';
        if (!is_file($path)) {
            return '';
        }

        ob_start();
        include $path;
        self::$notifyPartialCache = (string) ob_get_clean();

        return self::$notifyPartialCache;
    }

    private function defaultFooterHtml(string $safeAppName): string
    {
        $year = date('Y');
        $dashboard = htmlspecialchars((string) __('auth.web.nav.dashboard'), ENT_QUOTES, 'UTF-8');
        $terms = htmlspecialchars((string) __('auth.web.common.terms'), ENT_QUOTES, 'UTF-8');

        return <<<HTML
      <!--  BEGIN FOOTER  -->
      <footer class="footer footer-transparent d-print-none">
        <div class="container-xl">
          <div class="row text-center align-items-center flex-row-reverse">
            <div class="col-lg-auto ms-lg-auto">
              <ul class="list-inline list-inline-dots mb-0">
                <li class="list-inline-item"><a href="/dashboard" class="link-secondary">{$dashboard}</a></li>
                <li class="list-inline-item"><a href="/tos" class="link-secondary">{$terms}</a></li>
              </ul>
            </div>
            <div class="col-12 col-lg-auto mt-3 mt-lg-0">
              <ul class="list-inline list-inline-dots mb-0">
                <li class="list-inline-item">
                  Copyright &copy; {$year} <a href="/" class="link-secondary">{$safeAppName}</a>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </footer>
      <!--  END FOOTER  -->
HTML;
    }
}
