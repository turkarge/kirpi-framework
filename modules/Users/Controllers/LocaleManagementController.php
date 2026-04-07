<?php

declare(strict_types=1);

namespace Modules\Users\Controllers;

use Core\Auth\DashboardShellRenderer;
use Core\Auth\Facades\Auth;
use Core\Http\Request;
use Core\Http\Response;

final class LocaleManagementController
{
    private const SUPPORTED = ['tr', 'en'];

    public function index(): Response
    {
        return $this->renderPage(
            title: (string) __('locales.meta_title'),
            headerHtml: $this->headerHtml(),
            bodyHtml: $this->bodyHtml()
        );
    }

    public function update(): Response
    {
        $request = app(Request::class);
        $defaultLocale = strtolower(trim((string) $request->input('default_locale', 'tr')));
        $enabledLocales = $request->input('enabled_locales', []);
        $enabledLocales = is_array($enabledLocales) ? $enabledLocales : [];
        $enabledLocales = array_values(array_unique(array_map(
            static fn(mixed $item): string => strtolower(trim((string) $item)),
            $enabledLocales
        )));
        $enabledLocales = array_values(array_filter(
            $enabledLocales,
            static fn(string $locale): bool => in_array($locale, self::SUPPORTED, true)
        ));

        if (!in_array($defaultLocale, self::SUPPORTED, true)) {
            flash((string) __('locales.flash.invalid_default'), 'warning', (string) __('locales.flash.warning_title'));
            return redirect('/locales');
        }

        if (!in_array($defaultLocale, $enabledLocales, true)) {
            $enabledLocales[] = $defaultLocale;
        }

        if ($enabledLocales === []) {
            flash((string) __('locales.flash.empty_enabled'), 'warning', (string) __('locales.flash.warning_title'));
            return redirect('/locales');
        }

        $this->setEnvValue('APP_LOCALE', $defaultLocale);
        $this->setEnvValue('APP_FALLBACK_LOCALE', $defaultLocale);
        $this->setEnvValue('APP_SUPPORTED_LOCALES', implode(',', $enabledLocales));

        flash((string) __('locales.flash.updated'), 'success', (string) __('locales.flash.success_title'));
        return redirect('/locales');
    }

    public function updateTranslations(): Response
    {
        $request = app(Request::class);
        $locale = strtolower(trim((string) $request->input('locale', 'tr')));
        $group = trim((string) $request->input('group', 'auth'));
        $keys = $request->input('keys', []);
        $values = $request->input('values', []);

        if (!in_array($locale, self::SUPPORTED, true)) {
            flash((string) __('locales.flash.invalid_locale'), 'warning', (string) __('locales.flash.warning_title'));
            return redirect('/locales');
        }

        if (!preg_match('/^[a-z0-9_\-]+$/', $group)) {
            flash((string) __('locales.flash.invalid_group'), 'warning', (string) __('locales.flash.warning_title'));
            return redirect('/locales');
        }

        $keys = is_array($keys) ? $keys : [];
        $values = is_array($values) ? $values : [];
        $target = $this->flatten($this->loadLocaleGroup($locale, $group));

        foreach ($keys as $index => $key) {
            $k = trim((string) $key);
            if ($k === '') {
                continue;
            }

            $target[$k] = (string) ($values[$index] ?? '');
        }

        $this->saveLocaleGroup($locale, $group, $this->unflatten($target));

        flash((string) __('locales.flash.translations_updated'), 'success', (string) __('locales.flash.success_title'));
        return redirect('/locales?locale=' . rawurlencode($locale) . '&group=' . rawurlencode($group));
    }

    private function renderPage(string $title, string $headerHtml, string $bodyHtml): Response
    {
        $user = Auth::guard('session')->user();
        $name = (string) ($user?->name ?? 'User');
        $email = (string) ($user?->email ?? '-');
        $appName = (string) config('app.name', 'Kirpi Framework');

        $renderer = new DashboardShellRenderer();
        $request = app(Request::class);
        $html = $renderer->render(
            title: $title,
            currentPath: $request->path(),
            appName: $appName,
            userName: $name,
            userEmail: $email,
            headerHtml: $headerHtml,
            bodyHtml: $bodyHtml
        );

        if ($html === null) {
            return Response::make('Locale template bulunamadi.', 500, ['Content-Type' => 'text/plain; charset=utf-8']);
        }

        return Response::make($html, 200, ['Content-Type' => 'text/html; charset=utf-8']);
    }

    private function headerHtml(): string
    {
        $pretitle = $this->e(__('locales.pretitle'));
        $title = $this->e(__('locales.title'));
        $subtitle = $this->e(__('locales.subtitle'));

        return <<<HTML
      <!-- BEGIN PAGE HEADER -->
      <div class="page-header d-print-none">
        <div class="container-xl">
          <div class="row g-2 align-items-center">
            <div class="col">
              <div class="page-pretitle">{$pretitle}</div>
              <h2 class="page-title">{$title}</h2>
              <div class="text-secondary">{$subtitle}</div>
            </div>
          </div>
        </div>
      </div>
      <!-- END PAGE HEADER -->
HTML;
    }

    private function bodyHtml(): string
    {
        $csrf = $this->csrfToken();
        $currentDefault = (string) env('APP_LOCALE', 'tr');
        $supportedRaw = (string) env('APP_SUPPORTED_LOCALES', 'tr,en');
        $enabled = $this->parseLocales($supportedRaw);
        if ($enabled === []) {
            $enabled = ['tr', 'en'];
        }

        $title = $this->e(__('locales.card.title'));
        $description = $this->e(__('locales.card.description'));
        $defaultLabel = $this->e(__('locales.form.default_locale'));
        $enabledLabel = $this->e(__('locales.form.enabled_locales'));
        $save = $this->e(__('locales.actions.save'));

        $defaultOptions = $this->localeOptionsHtml($currentDefault);
        $enabledChecks = $this->localeChecksHtml($enabled);

        $currentTitle = $this->e(__('locales.current.title'));
        $currentDefaultLabel = $this->e(__('locales.current.default_locale'));
        $currentEnabledLabel = $this->e(__('locales.current.enabled_locales'));
        $currentEnabledValue = $this->e(implode(', ', array_map('strtoupper', $enabled)));
        $currentDefaultValue = $this->e(strtoupper($currentDefault));

        $request = app(Request::class);
        $locale = strtolower(trim((string) $request->get('locale', $currentDefault)));
        if (!in_array($locale, self::SUPPORTED, true)) {
            $locale = $currentDefault;
        }

        $groups = $this->availableGroups($locale);
        $group = trim((string) $request->get('group', $groups[0] ?? 'auth'));
        if (!in_array($group, $groups, true)) {
            $group = $groups[0] ?? 'auth';
        }

        $targetFlat = $this->flatten($this->loadLocaleGroup($locale, $group));
        $tableRows = $this->translationRowsHtml($targetFlat);

        $quickTitle = $this->e(__('locales.translations.title'));
        $quickDescription = $this->e(__('locales.translations.description'));
        $localeLabel = $this->e(__('locales.form.edit_locale'));
        $groupLabel = $this->e(__('locales.form.group'));
        $load = $this->e(__('locales.actions.edit_locale'));
        $saveTranslations = $this->e(__('locales.actions.save_translations'));
        $filterPlaceholder = $this->e(__('locales.form.filter'));
        $keyTh = $this->e(__('locales.table.key'));
        $targetTh = $this->e(__('locales.table.target'));

        $sourceOptions = $this->localeOptionsHtml($locale);
        $groupOptions = $this->groupOptionsHtml($groups, $group);
        $localeHidden = $this->e($locale);
        $groupHidden = $this->e($group);

        return <<<HTML
      <!-- BEGIN PAGE BODY -->
      <div class="page-body">
        <div class="container-xl">
          <div class="row row-cards">
            <div class="col-12 col-lg-8">
              <div class="card">
                <div class="card-header">
                  <h3 class="card-title">{$title}</h3>
                </div>
                <div class="card-body">
                  <p class="text-secondary">{$description}</p>
                  <form method="POST" action="/locales">
                    <input type="hidden" name="_method" value="PUT">
                    <input type="hidden" name="_token" value="{$csrf}">
                    <div class="mb-3">
                      <label class="form-label">{$defaultLabel}</label>
                      <select class="form-select" name="default_locale">
{$defaultOptions}
                      </select>
                    </div>
                    <div class="mb-4">
                      <label class="form-label d-block">{$enabledLabel}</label>
{$enabledChecks}
                    </div>
                    <button class="btn btn-primary" type="submit">{$save}</button>
                  </form>
                </div>
              </div>
            </div>
            <div class="col-12 col-lg-4">
              <div class="card">
                <div class="card-header">
                  <h3 class="card-title">{$currentTitle}</h3>
                </div>
                <div class="card-body">
                  <dl class="row mb-0">
                    <dt class="col-6">{$currentDefaultLabel}</dt>
                    <dd class="col-6">{$currentDefaultValue}</dd>
                    <dt class="col-6">{$currentEnabledLabel}</dt>
                    <dd class="col-6">{$currentEnabledValue}</dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <div class="row row-cards mt-1">
            <div class="col-12">
              <div class="card">
                <div class="card-header">
                  <h3 class="card-title">{$quickTitle}</h3>
                </div>
                <div class="card-body">
                  <p class="text-secondary">{$quickDescription}</p>

                  <form method="GET" action="/locales" class="row g-3 mb-4">
                    <div class="col-12 col-md-4">
                      <label class="form-label">{$localeLabel}</label>
                      <select class="form-select" name="locale">
{$sourceOptions}
                      </select>
                    </div>
                    <div class="col-12 col-md-4">
                      <label class="form-label">{$groupLabel}</label>
                      <select class="form-select" name="group">
{$groupOptions}
                      </select>
                    </div>
                    <div class="col-12 col-md-4 d-flex align-items-end">
                      <button class="btn btn-outline-primary w-100" type="submit">{$load}</button>
                    </div>
                  </form>

                  <form method="POST" action="/locales/translations">
                    <input type="hidden" name="_method" value="PUT">
                    <input type="hidden" name="_token" value="{$csrf}">
                    <input type="hidden" name="locale" value="{$localeHidden}">
                    <input type="hidden" name="group" value="{$groupHidden}">

                    <div class="mb-3">
                      <input id="translation-filter" type="text" class="form-control" placeholder="{$filterPlaceholder}">
                    </div>

                    <div class="table-responsive" style="max-height: 460px;">
                      <table class="table table-vcenter">
                        <thead>
                          <tr>
                            <th style="width: 24%;">{$keyTh}</th>
                            <th style="width: 76%;">{$targetTh}</th>
                          </tr>
                        </thead>
                        <tbody id="translation-table-body">
{$tableRows}
                        </tbody>
                      </table>
                    </div>

                    <div class="mt-3">
                      <button class="btn btn-primary" type="submit">{$saveTranslations}</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <script>
        (() => {
          const input = document.getElementById('translation-filter');
          const body = document.getElementById('translation-table-body');
          if (!input || !body) return;

          input.addEventListener('input', () => {
            const q = input.value.trim().toLowerCase();
            body.querySelectorAll('tr').forEach((row) => {
              const hay = (row.dataset.key || '') + ' ' + (row.dataset.target || '');
              row.style.display = q === '' || hay.toLowerCase().includes(q) ? '' : 'none';
            });
          });
        })();
      </script>
      <!-- END PAGE BODY -->
HTML;
    }

    private function localeOptionsHtml(string $selected): string
    {
        $options = '';
        foreach (self::SUPPORTED as $locale) {
            $label = strtoupper($locale);
            $selectedAttr = $selected === $locale ? ' selected' : '';
            $options .= '                        <option value="' . $this->e($locale) . '"' . $selectedAttr . '>' . $this->e($label) . '</option>' . PHP_EOL;
        }

        return rtrim($options);
    }

    private function localeChecksHtml(array $enabled): string
    {
        $checks = '';
        foreach (self::SUPPORTED as $locale) {
            $label = strtoupper($locale);
            $checked = in_array($locale, $enabled, true) ? ' checked' : '';
            $checks .= <<<HTML
                      <label class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="enabled_locales[]" value="{$this->e($locale)}"{$checked}>
                        <span class="form-check-label">{$this->e($label)}</span>
                      </label>
HTML;
        }

        return $checks;
    }

    /**
     * @return list<string>
     */
    private function availableGroups(string $locale): array
    {
        $groups = $this->groupsForLocale($locale);
        $groups = array_values(array_unique($groups));
        sort($groups);
        return $groups;
    }

    /**
     * @return list<string>
     */
    private function groupsForLocale(string $locale): array
    {
        $dir = BASE_PATH . '/lang/' . $locale;
        if (!is_dir($dir)) {
            return [];
        }

        $files = glob($dir . '/*.php') ?: [];
        $groups = array_map(static fn(string $file): string => pathinfo($file, PATHINFO_FILENAME), $files);
        sort($groups);
        return array_values($groups);
    }

    private function groupOptionsHtml(array $groups, string $selected): string
    {
        $html = '';
        foreach ($groups as $group) {
            $selectedAttr = $group === $selected ? ' selected' : '';
            $safe = $this->e($group);
            $html .= '                        <option value="' . $safe . '"' . $selectedAttr . '>' . $safe . '</option>' . PHP_EOL;
        }

        return rtrim($html);
    }

    private function loadLocaleGroup(string $locale, string $group): array
    {
        $path = BASE_PATH . '/lang/' . $locale . '/' . $group . '.php';
        if (!is_file($path)) {
            return [];
        }

        $data = require $path;
        return is_array($data) ? $data : [];
    }

    private function saveLocaleGroup(string $locale, string $group, array $data): void
    {
        $dir = BASE_PATH . '/lang/' . $locale;
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $path = $dir . '/' . $group . '.php';
        $content = "<?php\n\ndeclare(strict_types=1);\n\nreturn " . var_export($data, true) . ";\n";
        file_put_contents($path, $content);
    }

    /**
     * @return array<string,string>
     */
    private function flatten(array $data, string $prefix = ''): array
    {
        $result = [];
        foreach ($data as $key => $value) {
            $k = $prefix === '' ? (string) $key : $prefix . '.' . (string) $key;
            if (is_array($value)) {
                $result += $this->flatten($value, $k);
                continue;
            }

            if (is_scalar($value) || $value === null) {
                $result[$k] = (string) ($value ?? '');
            }
        }

        ksort($result);
        return $result;
    }

    private function unflatten(array $flat): array
    {
        $tree = [];
        foreach ($flat as $key => $value) {
            $segments = explode('.', (string) $key);
            $ref = &$tree;
            foreach ($segments as $segment) {
                if (!isset($ref[$segment]) || !is_array($ref[$segment])) {
                    $ref[$segment] = [];
                }
                $ref = &$ref[$segment];
            }
            $ref = (string) $value;
            unset($ref);
        }

        return $tree;
    }

    private function translationRowsHtml(array $targetFlat): string
    {
        $rows = '';
        foreach ($targetFlat as $key => $targetValue) {
            $safeKey = $this->e($key);
            $safeTarget = $this->e($targetValue);
            $rows .= <<<HTML
                          <tr data-key="{$safeKey}" data-target="{$safeTarget}">
                            <td><code>{$safeKey}</code><input type="hidden" name="keys[]" value="{$safeKey}"></td>
                            <td><input type="text" class="form-control" name="values[]" value="{$safeTarget}"></td>
                          </tr>
HTML;
        }

        if ($rows === '') {
            $empty = $this->e((string) __('locales.table.empty'));
            return <<<HTML
                          <tr>
                            <td colspan="2" class="text-secondary text-center py-4">{$empty}</td>
                          </tr>
HTML;
        }

        return $rows;
    }

    private function parseLocales(string $value): array
    {
        $items = array_map(
            static fn(string $item): string => strtolower(trim($item)),
            explode(',', $value)
        );

        $items = array_values(array_filter(
            array_unique($items),
            static fn(string $locale): bool => in_array($locale, self::SUPPORTED, true)
        ));

        return $items;
    }

    private function setEnvValue(string $key, string $value): void
    {
        $envPath = BASE_PATH . '/.env';
        if (!is_file($envPath)) {
            return;
        }

        $content = (string) file_get_contents($envPath);
        $line = $key . '=' . $value;
        $pattern = '/^' . preg_quote($key, '/') . '=.*$/m';

        if (preg_match($pattern, $content) === 1) {
            $content = (string) preg_replace($pattern, $line, $content);
        } else {
            if ($content !== '' && !str_ends_with($content, "\n")) {
                $content .= "\n";
            }
            $content .= $line . "\n";
        }

        file_put_contents($envPath, $content);
        $_ENV[$key] = $value;
        putenv($key . '=' . $value);
    }

    private function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    private function csrfToken(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['_token']) || !is_string($_SESSION['_token'])) {
            $_SESSION['_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['_token'];
    }
}
