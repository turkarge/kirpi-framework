<?php

declare(strict_types=1);

namespace Modules\Roles\Controllers;

use Core\Auth\DashboardShellRenderer;
use Core\Auth\Facades\Auth;
use Core\Http\Request;
use Core\Http\Response;
use Modules\Roles\Models\Role;

final class RoleManagementController
{
    public function index(): Response
    {
        return $this->renderPage(
            title: (string) __('roles.meta_title'),
            headerHtml: $this->indexHeaderHtml(),
            bodyHtml: $this->indexBodyHtml()
        );
    }

    public function show(string $role): Response
    {
        return $this->renderPage(
            title: (string) __('roles.detail.meta_title', ['role' => $role]),
            headerHtml: $this->detailHeaderHtml($role, false),
            bodyHtml: $this->detailBodyHtml($role, false)
        );
    }

    public function edit(string $role): Response
    {
        return $this->renderPage(
            title: (string) __('roles.edit.meta_title', ['role' => $role]),
            headerHtml: $this->detailHeaderHtml($role, true),
            bodyHtml: $this->detailBodyHtml($role, true)
        );
    }

    private function renderPage(string $title, string $headerHtml, string $bodyHtml): Response
    {
        $user = Auth::guard('session')->user();
        $name = (string) ($user?->name ?? 'User');
        $email = (string) ($user?->email ?? '-');
        $appName = (string) config('app.name', 'Kirpi Framework');

        $renderer = new DashboardShellRenderer();
        $html = $renderer->render(
            title: $title,
            currentPath: '/roles',
            appName: $appName,
            userName: $name,
            userEmail: $email,
            headerHtml: $headerHtml,
            bodyHtml: $bodyHtml,
            footerHtml: $this->footerHtml(htmlspecialchars($appName, ENT_QUOTES, 'UTF-8'))
        );

        if ($html === null) {
            return Response::make('Roles template bulunamadi.', 500, ['Content-Type' => 'text/plain; charset=utf-8']);
        }

        return Response::make($html, 200, ['Content-Type' => 'text/html; charset=utf-8']);
    }

    private function indexHeaderHtml(): string
    {
        $pretitle = $this->e(__('roles.pretitle'));
        $title = $this->e(__('roles.title'));
        $subtitle = $this->e(__('roles.subtitle'));
        $matrix = $this->e(__('roles.actions.matrix'));
        $new = $this->e(__('roles.actions.new'));

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
            <div class="col-auto ms-auto d-print-none">
              <div class="btn-list">
                <a href="#" class="btn btn-outline-primary">{$matrix}</a>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-new-role">{$new}</button>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- END PAGE HEADER -->
HTML;
    }

    private function indexBodyHtml(): string
    {
        $request = app(Request::class);
        $statusFilter = strtolower((string) $request->get('status', 'active'));
        if (!in_array($statusFilter, ['active', 'passive', 'all'], true)) {
            $statusFilter = 'active';
        }
        $searchQuery = trim((string) $request->get('q', ''));

        $active = (string) __('roles.status.active');
        $passive = (string) __('roles.status.passive');
        $editLabel = $this->e(__('roles.actions.edit'));
        $permissionsLabel = $this->e(__('roles.permissions.title'));
        $rolesQuery = Role::query()
            ->select('name', 'slug', 'is_active', 'user_count', 'updated_at')
            ->orderBy('sort_order', 'ASC')
            ->orderBy('name', 'ASC');

        if ($statusFilter === 'active') {
            $rolesQuery->where('is_active', 1);
        } elseif ($statusFilter === 'passive') {
            $rolesQuery->where('is_active', 0);
        }

        if ($searchQuery !== '') {
            $like = '%' . $searchQuery . '%';
            $rolesQuery->where(static function ($query) use ($like): void {
                $query->where('name', 'LIKE', $like)
                    ->orWhere('slug', 'LIKE', $like);
            });
        }

        $roles = $rolesQuery->get();

        $rows = '';
        foreach ($roles as $role) {
            $name = $this->e((string) ($role->name ?? ''));
            $users = $this->e((string) ($role->user_count ?? 0));
            $updated = $this->e($this->formatDateTime($role->updated_at ?? null));
            $slug = rawurlencode((string) ($role->slug ?? $role->name ?? ''));
            $isActive = (int) ($role->is_active ?? 0) === 1;
            $statusLabel = $isActive ? $this->e($active) : $this->e($passive);
            $switchChecked = $isActive ? ' checked' : '';
            $switch = <<<HTML
<div class="form-check form-switch m-0">
  <input class="form-check-input" type="checkbox" role="switch"{$switchChecked} disabled aria-label="{$statusLabel}">
</div>
HTML;

            $rows .= <<<HTML
                      <tr>
                        <td><strong>{$name}</strong></td>
                        <td>{$users}</td>
                        <td>{$updated}</td>
                        <td>{$switch}</td>
                        <td class="text-end">
                          <div class="btn-list justify-content-end flex-nowrap">
                            <a class="btn btn-outline-primary btn-sm d-inline-flex align-items-center gap-1" href="/roles/{$slug}/edit">
                              <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-2 m-0" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 20h9" />
                                <path d="M16.5 3.5a2.121 2.121 0 1 1 3 3L7 19l-4 1 1-4z" />
                              </svg>
                              <span>{$editLabel}</span>
                            </a>
                            <a class="btn btn-outline-teal btn-sm d-inline-flex align-items-center gap-1" href="/roles/{$slug}">
                              <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-2 m-0" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 3l8 4v6c0 5-3.5 8.5-8 10-4.5-1.5-8-5-8-10V7z" />
                                <path d="M9 12l2 2 4-4" />
                              </svg>
                              <span>{$permissionsLabel}</span>
                            </a>
                          </div>
                        </td>
                      </tr>
HTML;
        }

        if ($rows === '') {
            $emptyText = $this->e(__('roles.table.empty'));
            $rows = <<<HTML
                      <tr>
                        <td colspan="5" class="text-secondary text-center py-4">{$emptyText}</td>
                      </tr>
HTML;
        }

        $tableTitle = $this->e(__('roles.table.title'));
        $search = $this->e(__('roles.filters.search'));
        $all = $this->e(__('roles.filters.all'));
        $roleTh = $this->e(__('roles.table.role'));
        $userCountTh = $this->e(__('roles.table.user_count'));
        $statusTh = $this->e(__('roles.table.status'));
        $updatedTh = $this->e(__('roles.table.updated_at'));
        $summaryTitle = $this->e(__('roles.side.title'));
        $summaryText = $this->e(__('roles.side.description'));
        $summaryHint = $this->e(__('roles.side.hint'));
        $modalTitle = $this->e(__('roles.modal.new_title'));
        $fieldName = $this->e(__('roles.form.name'));
        $fieldSlug = $this->e(__('roles.form.slug'));
        $fieldDesc = $this->e(__('roles.form.description'));
        $cancel = $this->e(__('roles.actions.cancel'));
        $create = $this->e(__('roles.actions.create'));

        return <<<HTML
      <!-- BEGIN PAGE BODY -->
      <div class="page-body">
        <div class="container-xl">
          <div class="row row-cards">
            <div class="col-12 col-lg-8">
              <div class="card">
                <div class="card-header">
                  <h3 class="card-title">{$tableTitle}</h3>
                </div>
                <div class="card-body border-bottom py-3">
                  <form method="GET" action="/roles" class="d-flex gap-2">
                    <div class="text-secondary flex-fill">
                      <input type="text" class="form-control" name="q" value="{$this->e($searchQuery)}" placeholder="{$search}">
                    </div>
                    <div class="text-secondary">
                      <select class="form-select" name="status" onchange="this.form.submit()">
                        <option value="all">{$all}</option>
                        <option value="active"{$this->selectedAttr($statusFilter, 'active')}>{$this->e($active)}</option>
                        <option value="passive"{$this->selectedAttr($statusFilter, 'passive')}>{$this->e($passive)}</option>
                      </select>
                    </div>
                  </form>
                </div>
                <div class="table-responsive">
                  <table class="table table-vcenter card-table">
                    <thead>
                      <tr>
                        <th>{$roleTh}</th>
                        <th>{$userCountTh}</th>
                        <th>{$updatedTh}</th>
                        <th>{$statusTh}</th>
                        <th class="text-end"></th>
                      </tr>
                    </thead>
                    <tbody>
{$rows}
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
            <div class="col-12 col-lg-4">
              <div class="card">
                <div class="card-header">
                  <h3 class="card-title">{$summaryTitle}</h3>
                </div>
                <div class="card-body">
                  <p class="text-secondary mb-3">{$summaryText}</p>
                  <div class="alert alert-info mb-0">{$summaryHint}</div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="modal modal-blur fade" id="modal-new-role" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">{$modalTitle}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <div class="mb-3">
                  <label class="form-label">{$fieldName}</label>
                  <input type="text" class="form-control" placeholder="manager">
                </div>
                <div class="mb-3">
                  <label class="form-label">{$fieldSlug}</label>
                  <input type="text" class="form-control" placeholder="manager">
                </div>
                <div class="mb-0">
                  <label class="form-label">{$fieldDesc}</label>
                  <textarea class="form-control" rows="3"></textarea>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">{$cancel}</button>
                <button type="button" class="btn btn-primary ms-auto">{$create}</button>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- END PAGE BODY -->
HTML;
    }

    private function detailHeaderHtml(string $role, bool $edit): string
    {
        $pretitle = $this->e(__('roles.pretitle'));
        $title = $edit ? $this->e(__('roles.edit.title')) : $this->e(__('roles.detail.title'));
        $subtitle = $this->e(__('roles.detail.subtitle', ['role' => $role]));
        $back = $this->e(__('roles.actions.back_to_list'));
        $backHref = '/roles';

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
            <div class="col-auto ms-auto d-print-none">
              <a class="btn btn-outline-primary" href="{$backHref}">{$back}</a>
            </div>
          </div>
        </div>
      </div>
      <!-- END PAGE HEADER -->
HTML;
    }

    private function detailBodyHtml(string $role, bool $edit): string
    {
        $roleSafe = $this->e($role);
        $fieldName = $this->e(__('roles.form.name'));
        $fieldSlug = $this->e(__('roles.form.slug'));
        $fieldDesc = $this->e(__('roles.form.description'));
        $fieldStatus = $this->e(__('roles.form.status'));
        $active = $this->e(__('roles.status.active'));
        $passive = $this->e(__('roles.status.passive'));
        $permTitle = $this->e(__('roles.permissions.title'));
        $save = $this->e(__('roles.actions.save'));
        $editButton = $this->e(__('roles.actions.edit'));
        $viewButton = $this->e(__('roles.actions.view'));

        $left = $edit
            ? <<<HTML
                <div class="card">
                  <div class="card-body">
                    <div class="mb-3">
                      <label class="form-label">{$fieldName}</label>
                      <input type="text" class="form-control" value="{$roleSafe}">
                    </div>
                    <div class="mb-3">
                      <label class="form-label">{$fieldSlug}</label>
                      <input type="text" class="form-control" value="{$roleSafe}">
                    </div>
                    <div class="mb-3">
                      <label class="form-label">{$fieldDesc}</label>
                      <textarea class="form-control" rows="3">Role detail description</textarea>
                    </div>
                    <div class="mb-4">
                      <label class="form-label">{$fieldStatus}</label>
                      <select class="form-select">
                        <option>{$active}</option>
                        <option>{$passive}</option>
                      </select>
                    </div>
                    <button class="btn btn-primary">{$save}</button>
                  </div>
                </div>
HTML
            : <<<HTML
                <div class="card">
                  <div class="card-body">
                    <dl class="row mb-0">
                      <dt class="col-4">{$fieldName}</dt><dd class="col-8">{$roleSafe}</dd>
                      <dt class="col-4">{$fieldSlug}</dt><dd class="col-8">{$roleSafe}</dd>
                      <dt class="col-4">{$fieldStatus}</dt><dd class="col-8"><span class="badge bg-green-lt">{$active}</span></dd>
                    </dl>
                    <div class="mt-4">
                      <a class="btn btn-outline-primary" href="/roles/{$roleSafe}/edit">{$editButton}</a>
                    </div>
                  </div>
                </div>
HTML;

        $right = <<<HTML
            <div class="card">
              <div class="card-header"><h3 class="card-title">{$permTitle}</h3></div>
              <div class="card-body">
                <label class="form-check">
                  <input class="form-check-input" type="checkbox" checked disabled>
                  <span class="form-check-label">users.view</span>
                </label>
                <label class="form-check">
                  <input class="form-check-input" type="checkbox" checked disabled>
                  <span class="form-check-label">users.edit</span>
                </label>
                <label class="form-check">
                  <input class="form-check-input" type="checkbox" disabled>
                  <span class="form-check-label">roles.delete</span>
                </label>
              </div>
            </div>
            <div class="card mt-3">
              <div class="card-header"><h3 class="card-title">{$this->e(__('roles.audit.title'))}</h3></div>
              <div class="card-body">
                <div class="text-secondary">{$this->e(__('roles.audit.placeholder'))}</div>
              </div>
            </div>
HTML;

        return <<<HTML
      <!-- BEGIN PAGE BODY -->
      <div class="page-body">
        <div class="container-xl">
          <div class="row row-cards">
            <div class="col-12 col-lg-8">
              {$left}
            </div>
            <div class="col-12 col-lg-4">
              {$right}
            </div>
          </div>
        </div>
      </div>
      <!-- END PAGE BODY -->
HTML;
    }

    private function footerHtml(string $appName): string
    {
        $year = date('Y');
        $dashboard = $this->e(__('roles.footer.dashboard'));
        $terms = $this->e(__('roles.footer.terms'));

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
                  Copyright &copy; {$year} <a href="/" class="link-secondary">{$appName}</a>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </footer>
      <!--  END FOOTER  -->
HTML;
    }

    private function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    private function formatDateTime(mixed $value): string
    {
        $format = (string) config('app.datetime_format', 'd.m.Y H:i');

        if ($value instanceof \DateTimeInterface) {
            return $value->format($format);
        }

        if (!is_string($value) || trim($value) === '') {
            return '-';
        }

        try {
            $date = new \DateTimeImmutable($value);
            return $date->format($format);
        } catch (\Throwable) {
            return (string) $value;
        }
    }

    private function selectedAttr(string $current, string $expected): string
    {
        return $current === $expected ? ' selected' : '';
    }
}
