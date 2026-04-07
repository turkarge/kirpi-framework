<?php

declare(strict_types=1);

namespace Modules\Dashboard\Controllers;

use Core\Auth\DashboardShellRenderer;
use Core\Auth\Facades\Auth;
use Core\Http\Response;
use Core\Routing\Router;
use Core\Runtime\RuntimeDiagnostics;
use Modules\Roles\Models\Role;
use Modules\Users\Models\User;

final class DashboardController
{
    public function index(): Response
    {
        $user = Auth::guard('session')->user();
        $name = (string) ($user?->name ?? 'User');
        $email = (string) ($user?->email ?? '-');
        $appName = (string) config('app.name', 'Kirpi Framework');
        $metrics = $this->collectMetrics();

        $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $safeEmail = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
        $safeAppName = htmlspecialchars($appName, ENT_QUOTES, 'UTF-8');

        $renderer = new DashboardShellRenderer();
        $html = $renderer->render(
            title: (string) __('dashboard.meta_title'),
            currentPath: '/dashboard',
            appName: $appName,
            userName: $name,
            userEmail: $email,
            headerHtml: $this->headerHtml($safeAppName),
            bodyHtml: $this->bodyHtml($safeName, $safeEmail, $safeAppName, $metrics)
        );

        if ($html === null) {
            return Response::make('Dashboard template bulunamadi.', 500, ['Content-Type' => 'text/plain; charset=utf-8']);
        }

        return Response::make($html, 200, ['Content-Type' => 'text/html; charset=utf-8']);
    }

    private function collectMetrics(): array
    {
        /** @var Router $router */
        $router = app(Router::class);
        /** @var RuntimeDiagnostics $diagnostics */
        $diagnostics = app(RuntimeDiagnostics::class);

        $checks = $diagnostics->checks();
        $db = (array) ($checks['database'] ?? []);
        $cache = (array) ($checks['cache'] ?? []);

        $usersTotal = null;
        $rolesTotal = null;

        try {
            $usersTotal = User::query()->count();
        } catch (\Throwable) {
            $usersTotal = null;
        }

        try {
            $rolesTotal = Role::query()->count();
        } catch (\Throwable) {
            $rolesTotal = null;
        }

        $moduleDirs = array_filter(
            glob(base_path('modules/*'), GLOB_ONLYDIR) ?: [],
            static fn(string $path): bool => basename($path) !== '' && basename($path)[0] !== '.'
        );

        return [
            'routes_total' => count($router->getRoutes()->all()),
            'modules_total' => count($moduleDirs),
            'users_total' => $usersTotal,
            'roles_total' => $rolesTotal,
            'database_status' => (string) ($db['status'] ?? 'down'),
            'database_latency' => isset($db['latency_ms']) ? (float) $db['latency_ms'] : null,
            'cache_status' => (string) ($cache['status'] ?? 'down'),
            'cache_latency' => isset($cache['latency_ms']) ? (float) $cache['latency_ms'] : null,
        ];
    }

    private function headerHtml(string $appName): string
    {
        $title = $this->e(__('dashboard.title'));
        $subtitle = $this->e(__('dashboard.subtitle'));
        $logout = $this->e(__('auth.web.common.logout'));
        $health = $this->e(__('dashboard.actions.health'));
        $ready = $this->e(__('dashboard.actions.ready'));

        return <<<HTML
      <!-- BEGIN PAGE HEADER -->
      <div class="page-header d-print-none">
        <div class="container-xl">
          <div class="row g-2 align-items-center">
            <div class="col">
              <div class="page-pretitle">{$appName}</div>
              <h2 class="page-title">{$title}</h2>
              <div class="text-secondary">{$subtitle}</div>
            </div>
            <div class="col-auto ms-auto d-print-none">
              <div class="btn-list">
                <a class="btn btn-outline-primary" href="/health" target="_blank" rel="noreferrer">{$health}</a>
                <a class="btn btn-outline-primary" href="/ready" target="_blank" rel="noreferrer">{$ready}</a>
                <a class="btn btn-primary" href="/exit">{$logout}</a>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- END PAGE HEADER -->
HTML;
    }

    private function bodyHtml(string $name, string $email, string $appName, array $metrics): string
    {
        $metricRoutes = $this->e(__('dashboard.metrics.routes'));
        $metricRoutesNote = $this->e(__('dashboard.metrics.routes_note'));
        $metricModules = $this->e(__('dashboard.metrics.modules'));
        $metricModulesNote = $this->e(__('dashboard.metrics.modules_note'));
        $metricDb = $this->e(__('dashboard.metrics.database'));
        $metricCache = $this->e(__('dashboard.metrics.cache'));
        $dbNote = $this->statusNote((string) ($metrics['database_status'] ?? 'down'), $metrics['database_latency'] ?? null);
        $cacheNote = $this->statusNote((string) ($metrics['cache_status'] ?? 'down'), $metrics['cache_latency'] ?? null);

        $dbStatusLabel = $this->statusLabel((string) ($metrics['database_status'] ?? 'down'));
        $cacheStatusLabel = $this->statusLabel((string) ($metrics['cache_status'] ?? 'down'));
        $dbStatusClass = $this->statusClass((string) ($metrics['database_status'] ?? 'down'));
        $cacheStatusClass = $this->statusClass((string) ($metrics['cache_status'] ?? 'down'));

        $welcomeTitle = $this->e(__('dashboard.welcome', ['name' => html_entity_decode($name, ENT_QUOTES, 'UTF-8')]));
        $welcomeDescription = $this->e(__('dashboard.description', ['app' => html_entity_decode($appName, ENT_QUOTES, 'UTF-8')]));
        $terms = $this->e(__('auth.web.common.terms'));
        $management = $this->e(__('auth.web.nav.management'));
        $accountSummary = $this->e(__('dashboard.account_summary'));
        $fieldUser = $this->e(__('auth.web.fields.user'));
        $fieldEmail = $this->e(__('auth.web.fields.email'));
        $fieldGuard = $this->e(__('auth.web.fields.guard'));
        $fieldUsers = $this->e(__('dashboard.fields.users_total'));
        $fieldRoles = $this->e(__('dashboard.fields.roles_total'));
        $fieldModules = $this->e(__('dashboard.fields.modules_total'));

        $usersTotal = $this->formatCount($metrics['users_total'] ?? null);
        $rolesTotal = $this->formatCount($metrics['roles_total'] ?? null);
        $modulesTotal = $this->formatCount($metrics['modules_total'] ?? null);
        $routesTotal = $this->formatCount($metrics['routes_total'] ?? null);

        $nextSteps = $this->e(__('dashboard.next_steps'));
        $stepCol = $this->e(__('dashboard.table.step_col'));
        $statusCol = $this->e(__('dashboard.table.status_col'));
        $noteCol = $this->e(__('dashboard.table.note_col'));
        $stepModule = $this->e(__('dashboard.table.step_module'));
        $stepCrud = $this->e(__('dashboard.table.step_crud'));
        $stepSecurity = $this->e(__('dashboard.table.step_security'));
        $steps = $this->stepStatuses($metrics);
        $stepModuleDetail = $this->e($steps['module']['detail']);
        $stepCrudDetail = $this->e($steps['crud']['detail']);
        $stepSecurityDetail = $this->e($steps['security']['detail']);

        return <<<HTML
      <!-- BEGIN PAGE BODY -->
      <div class="page-body">
        <div class="container-xl">
          <div class="row row-deck row-cards">
            <div class="col-12 col-sm-6 col-lg-3">
              <div class="card card-sm">
                <div class="card-body">
                  <div class="subheader">{$metricRoutes}</div>
                  <div class="h1 mb-0 mt-1">{$routesTotal}</div>
                  <div class="text-secondary">{$metricRoutesNote}</div>
                </div>
              </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-3">
              <div class="card card-sm">
                <div class="card-body">
                  <div class="subheader">{$metricModules}</div>
                  <div class="h1 mb-0 mt-1">{$modulesTotal}</div>
                  <div class="text-secondary">{$metricModulesNote}</div>
                </div>
              </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-3">
              <div class="card card-sm">
                <div class="card-body">
                  <div class="subheader">{$metricDb}</div>
                  <div class="h1 mb-0 mt-1 {$dbStatusClass}">{$dbStatusLabel}</div>
                  <div class="text-secondary">{$dbNote}</div>
                </div>
              </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-3">
              <div class="card card-sm">
                <div class="card-body">
                  <div class="subheader">{$metricCache}</div>
                  <div class="h1 mb-0 mt-1 {$cacheStatusClass}">{$cacheStatusLabel}</div>
                  <div class="text-secondary">{$cacheNote}</div>
                </div>
              </div>
            </div>

            <div class="col-12 col-lg-8">
              <div class="card">
                <div class="card-header">
                  <h3 class="card-title">{$welcomeTitle}</h3>
                </div>
                <div class="card-body">
                  <p class="text-secondary mb-3">{$welcomeDescription}</p>
                  <div class="btn-list">
                    <a class="btn btn-primary" href="/users">{$management}</a>
                    <a class="btn btn-outline-primary" href="/roles">{$this->e(__('auth.web.nav.roles'))}</a>
                    <a class="btn btn-outline-primary" href="/locales">{$this->e(__('auth.web.nav.locales'))}</a>
                    <a class="btn btn-outline-primary" href="/tos">{$terms}</a>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-12 col-lg-4">
              <div class="card">
                <div class="card-header">
                  <h3 class="card-title">{$accountSummary}</h3>
                </div>
                <div class="card-body">
                  <div class="mb-2"><span class="text-secondary">{$fieldUser}:</span> {$name}</div>
                  <div class="mb-2"><span class="text-secondary">{$fieldEmail}:</span> {$email}</div>
                  <div class="mb-2"><span class="text-secondary">{$fieldGuard}:</span> session</div>
                  <div class="mb-2"><span class="text-secondary">{$fieldUsers}:</span> {$usersTotal}</div>
                  <div class="mb-2"><span class="text-secondary">{$fieldRoles}:</span> {$rolesTotal}</div>
                  <div><span class="text-secondary">{$fieldModules}:</span> {$modulesTotal}</div>
                </div>
              </div>
            </div>

            <div class="col-12">
              <div class="card">
                <div class="card-header">
                  <h3 class="card-title">{$nextSteps}</h3>
                </div>
                <div class="table-responsive">
                  <table class="table table-vcenter card-table">
                    <thead>
                      <tr>
                        <th>{$stepCol}</th>
                        <th>{$statusCol}</th>
                        <th>{$noteCol}</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td>make:module</td>
                        <td><span class="badge {$steps['module']['class']}">{$steps['module']['label']}</span></td>
                        <td>
                          <div>{$stepModule}</div>
                          <div class="text-secondary small">{$stepModuleDetail}</div>
                        </td>
                      </tr>
                      <tr>
                        <td>make:crud</td>
                        <td><span class="badge {$steps['crud']['class']}">{$steps['crud']['label']}</span></td>
                        <td>
                          <div>{$stepCrud}</div>
                          <div class="text-secondary small">{$stepCrudDetail}</div>
                        </td>
                      </tr>
                      <tr>
                        <td>security baseline</td>
                        <td><span class="badge {$steps['security']['class']}">{$steps['security']['label']}</span></td>
                        <td>
                          <div>{$stepSecurity}</div>
                          <div class="text-secondary small">{$stepSecurityDetail}</div>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- END PAGE BODY -->
HTML;
    }

    private function stepStatuses(array $metrics): array
    {
        $readyText = $this->e(__('dashboard.table.ready'));
        $pendingText = $this->e(__('dashboard.table.pending'));
        $ok = (string) __('dashboard.table.detail_ok');

        $modulesTotal = (int) ($metrics['modules_total'] ?? 0);
        $rolesTotal = (int) ($metrics['roles_total'] ?? 0);
        $usersTotal = (int) ($metrics['users_total'] ?? 0);
        $dbUp = ($metrics['database_status'] ?? 'down') === 'up';
        $cacheUp = ($metrics['cache_status'] ?? 'down') === 'up';

        $moduleReady = $modulesTotal > 0;
        $crudReady = $rolesTotal > 0 && $usersTotal > 0;
        $securityReady = $dbUp && $cacheUp && $rolesTotal > 0;

        return [
            'module' => [
                'label' => $moduleReady ? $readyText : $pendingText,
                'class' => $moduleReady ? 'bg-green-lt' : 'bg-yellow-lt',
                'detail' => $moduleReady
                    ? $ok
                    : (string) __('dashboard.table.detail_module_pending', ['count' => (string) $modulesTotal]),
            ],
            'crud' => [
                'label' => $crudReady ? $readyText : $pendingText,
                'class' => $crudReady ? 'bg-green-lt' : 'bg-yellow-lt',
                'detail' => $crudReady
                    ? $ok
                    : (string) __('dashboard.table.detail_crud_pending', [
                        'users' => (string) $usersTotal,
                        'roles' => (string) $rolesTotal,
                    ]),
            ],
            'security' => [
                'label' => $securityReady ? $readyText : $pendingText,
                'class' => $securityReady ? 'bg-green-lt' : 'bg-yellow-lt',
                'detail' => $securityReady
                    ? $ok
                    : (string) __('dashboard.table.detail_security_pending', [
                        'db' => $dbUp ? 'up' : 'down',
                        'cache' => $cacheUp ? 'up' : 'down',
                        'roles' => (string) $rolesTotal,
                    ]),
            ],
        ];
    }

    private function statusLabel(string $status): string
    {
        return $status === 'up'
            ? $this->e(__('dashboard.status.up'))
            : $this->e(__('dashboard.status.down'));
    }

    private function statusClass(string $status): string
    {
        return $status === 'up' ? 'text-green' : 'text-red';
    }

    private function statusNote(string $status, ?float $latencyMs): string
    {
        $latency = $latencyMs !== null ? number_format($latencyMs, 2, '.', '') : null;

        if ($status === 'up') {
            return $latency !== null
                ? $this->e(__('dashboard.status.latency_up', ['ms' => $latency]))
                : $this->e(__('dashboard.status.up'));
        }

        return $latency !== null
            ? $this->e(__('dashboard.status.latency_down', ['ms' => $latency]))
            : $this->e(__('dashboard.status.down'));
    }

    private function formatCount(int|string|null $value): string
    {
        if ($value === null || $value === '') {
            return $this->e(__('dashboard.status.na'));
        }

        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }

    private function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
