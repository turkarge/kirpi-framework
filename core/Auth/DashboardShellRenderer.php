<?php

declare(strict_types=1);

namespace Core\Auth;

use Core\Auth\Facades\Auth;

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
        $html = $this->replaceUserMenu($html, $safeName, $safeEmail);
        $html = $this->injectLockShortcut($html);

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
        $logs = htmlspecialchars((string) __('auth.web.nav.logs'), ENT_QUOTES, 'UTF-8');

        $canDashboard = $this->can('dashboard.view');
        $canRoles = $this->can('roles.view');
        $canUsers = $this->can('users.view');
        $canLocales = $this->can('locales.view');
        $canLogs = $this->can('logs.view');

        $dashboardActive = str_starts_with($currentPath, '/dashboard') ? ' active' : '';
        $managementActive = (
            str_starts_with($currentPath, '/roles')
            || str_starts_with($currentPath, '/users')
            || str_starts_with($currentPath, '/locales')
            || str_starts_with($currentPath, '/logs')
        ) ? ' active' : '';

        $rolesItemClass = str_starts_with($currentPath, '/roles') ? 'dropdown-item active' : 'dropdown-item';
        $usersItemClass = str_starts_with($currentPath, '/users') ? 'dropdown-item active' : 'dropdown-item';
        $localesItemClass = str_starts_with($currentPath, '/locales') ? 'dropdown-item active' : 'dropdown-item';
        $logsItemClass = str_starts_with($currentPath, '/logs') ? 'dropdown-item active' : 'dropdown-item';
        $rolesAria = str_starts_with($currentPath, '/roles') ? ' aria-current="true"' : '';
        $usersAria = str_starts_with($currentPath, '/users') ? ' aria-current="true"' : '';
        $localesAria = str_starts_with($currentPath, '/locales') ? ' aria-current="true"' : '';
        $logsAria = str_starts_with($currentPath, '/logs') ? ' aria-current="true"' : '';

        $dashboardItem = $canDashboard
            ? <<<HTML
            <li class="nav-item{$dashboardActive}">
              <a class="nav-link" href="/dashboard">
                <span class="nav-link-title"> {$dashboard} </span>
              </a>
            </li>
HTML
            : '';

        $managementItems = '';
        if ($canRoles) {
            $managementItems .= '<a class="' . $rolesItemClass . '" href="/roles"' . $rolesAria . '>' . $roles . '</a>';
        }
        if ($canUsers) {
            $managementItems .= '<a class="' . $usersItemClass . '" href="/users"' . $usersAria . '>' . $users . '</a>';
        }
        if ($canLocales) {
            $managementItems .= '<a class="' . $localesItemClass . '" href="/locales"' . $localesAria . '>' . $locales . '</a>';
        }
        if ($canLogs) {
            $managementItems .= '<a class="' . $logsItemClass . '" href="/logs"' . $logsAria . '>' . $logs . '</a>';
        }

        $managementMenu = $managementItems !== ''
            ? <<<HTML
            <li class="nav-item dropdown{$managementActive}">
              <a class="nav-link dropdown-toggle" href="#navbar-management" data-bs-toggle="dropdown" data-bs-auto-close="outside" role="button" aria-expanded="false">
                <span class="nav-link-title"> {$management} </span>
              </a>
              <div class="dropdown-menu">
                {$managementItems}
              </div>
            </li>
HTML
            : '';

        return <<<HTML
          <!-- BEGIN NAVBAR MENU -->
          <ul class="navbar-nav">
            {$dashboardItem}
            {$managementMenu}
          </ul>
          <!-- END NAVBAR MENU -->
HTML;
    }

    private function can(string $permission): bool
    {
        try {
            if (Auth::guest()) {
                return false;
            }

            $user = Auth::user();
            if ($user === null || !method_exists($user, 'can')) {
                return false;
            }

            return (bool) $user->can($permission);
        } catch (\Throwable) {
            return false;
        }
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

    private function replaceUserMenu(string $html, string $safeName, string $safeEmail): string
    {
        $pattern = '/<div class="nav-item dropdown">[\s\S]*?<div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">[\s\S]*?<\/div>\s*<\/div>/i';
        $replacement = $this->userMenuHtml($safeName, $safeEmail);

        return (string) preg_replace($pattern, $replacement, $html, 1);
    }

    private function userMenuHtml(string $safeName, string $safeEmail): string
    {
        $initials = htmlspecialchars($this->initials($safeName), ENT_QUOTES, 'UTF-8');
        $account = htmlspecialchars((string) __('auth.web.user_menu.account'), ENT_QUOTES, 'UTF-8');
        $profile = htmlspecialchars((string) __('auth.web.user_menu.profile'), ENT_QUOTES, 'UTF-8');
        $lock = htmlspecialchars((string) __('auth.web.user_menu.lock'), ENT_QUOTES, 'UTF-8');
        $terms = htmlspecialchars((string) __('auth.web.common.terms'), ENT_QUOTES, 'UTF-8');
        $logout = htmlspecialchars((string) __('auth.web.common.logout'), ENT_QUOTES, 'UTF-8');

        $accountItem = '';
        $profileItem = '';

        try {
            $user = Auth::user();
            $id = isset($user->id) ? (int) $user->id : 0;
            if ($id > 0 && $this->can('users.view')) {
                $accountItem = '<a href="/users/' . $id . '" class="dropdown-item">' . $account . '</a>';
            }
            if ($id > 0 && $this->can('users.update')) {
                $profileItem = '<a href="/users/' . $id . '/edit" class="dropdown-item">' . $profile . '</a>';
            }
        } catch (\Throwable) {
            // Leave optional menu items hidden when user context is unavailable.
        }

        return <<<HTML
          <div class="nav-item dropdown">
            <a href="#" class="nav-link d-flex lh-1 p-0 px-2" data-bs-toggle="dropdown" aria-label="Open user menu">
              <span class="avatar avatar-sm bg-primary-lt">{$initials}</span>
              <div class="d-none d-xl-block ps-2">
                <div>{$safeName}</div>
                <div class="mt-1 small text-secondary">{$safeEmail}</div>
              </div>
            </a>
            <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
              <div class="dropdown-header">
                <div class="fw-semibold">{$safeName}</div>
                <div class="text-secondary small">{$safeEmail}</div>
              </div>
              {$accountItem}
              {$profileItem}
              <a href="/lock?lock=1" class="dropdown-item" onclick="this.href='/lock?lock=1&return='+encodeURIComponent(window.location.pathname+window.location.search)">{$lock}</a>
              <a href="/tos" class="dropdown-item">{$terms}</a>
              <div class="dropdown-divider"></div>
              <a href="/exit" class="dropdown-item text-danger">{$logout}</a>
            </div>
          </div>
HTML;
    }

    private function injectLockShortcut(string $html): string
    {
        $lockTitle = htmlspecialchars((string) __('auth.web.user_menu.lock'), ENT_QUOTES, 'UTF-8');
        $lockShortcut = <<<HTML
            <div class="nav-item">
              <a href="/lock?lock=1" class="nav-link px-0" title="{$lockTitle}" data-bs-toggle="tooltip" data-bs-placement="bottom" onclick="this.href='/lock?lock=1&return='+encodeURIComponent(window.location.pathname+window.location.search)">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-1">
                  <rect x="5" y="11" width="14" height="10" rx="2"></rect>
                  <path d="M8 11v-3a4 4 0 0 1 8 0v3"></path>
                </svg>
              </a>
            </div>
HTML;

        return (string) preg_replace(
            '/(<div class="nav-item">\s*<a href="[^"]*\?theme=dark"[\s\S]*?<\/a>\s*<a href="[^"]*\?theme=light"[\s\S]*?<\/a>\s*<\/div>)/i',
            '$1' . $lockShortcut,
            $html,
            1
        );
    }

    private function initials(string $safeName): string
    {
        $name = trim(html_entity_decode($safeName, ENT_QUOTES, 'UTF-8'));
        if ($name === '') {
            return 'KF';
        }

        $parts = preg_split('/\s+/u', $name) ?: [];
        $first = $parts[0] ?? '';
        $last = $parts[count($parts) - 1] ?? '';

        $a = mb_substr((string) $first, 0, 1, 'UTF-8');
        $b = mb_substr((string) $last, 0, 1, 'UTF-8');
        $initials = strtoupper(trim($a . $b));

        return $initials !== '' ? $initials : 'KF';
    }
}
