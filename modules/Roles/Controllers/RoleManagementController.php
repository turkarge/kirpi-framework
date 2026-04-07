<?php

declare(strict_types=1);

namespace Modules\Roles\Controllers;

use Core\Auth\DashboardShellRenderer;
use Core\Auth\Facades\Auth;
use Core\Http\Request;
use Core\Http\Response;
use Modules\Roles\Models\Role;
use Modules\Roles\Models\RolePermission;

final class RoleManagementController
{
    public function store(): Response
    {
        $request = app(Request::class);
        $name = trim((string) $request->input('name', ''));
        $slugInput = trim((string) $request->input('slug', ''));
        $description = trim((string) $request->input('description', ''));
        $isActive = $request->boolean('is_active', true) ? 1 : 0;

        if ($name === '') {
            flash((string) __('roles.flash.validation_failed'), 'warning', (string) __('roles.flash.warning_title'));
            return redirect('/roles');
        }

        $slug = $slugInput !== '' ? $this->slugify($slugInput) : $this->slugify($name);
        if ($slug === '') {
            flash((string) __('roles.flash.validation_failed'), 'warning', (string) __('roles.flash.warning_title'));
            return redirect('/roles');
        }

        $exists = Role::query()->where('slug', $slug)->exists();
        if ($exists) {
            flash((string) __('roles.flash.slug_taken'), 'warning', (string) __('roles.flash.warning_title'));
            return redirect('/roles');
        }

        $maxSortOrder = Role::query()->max('sort_order');
        $nextSortOrder = is_numeric($maxSortOrder) ? ((int) $maxSortOrder + 10) : 100;

        Role::create([
            'name' => $name,
            'slug' => $slug,
            'description' => $description !== '' ? $description : null,
            'is_active' => $isActive,
            'is_system' => 0,
            'user_count' => 0,
            'sort_order' => $nextSortOrder,
        ]);

        flash((string) __('roles.flash.created'), 'success', (string) __('roles.flash.success_title'));
        return redirect('/roles');
    }

    public function index(): Response
    {
        return $this->renderPage(
            title: (string) __('roles.meta_title'),
            headerHtml: $this->indexHeaderHtml(),
            bodyHtml: $this->indexBodyHtml()
        );
    }

    public function matrix(): Response
    {
        return $this->renderPage(
            title: (string) __('roles.matrix.meta_title'),
            headerHtml: $this->matrixHeaderHtml(),
            bodyHtml: $this->matrixBodyHtml()
        );
    }

    public function updateMatrix(): Response
    {
        $request = app(Request::class);
        $roles = Role::query()
            ->select('id')
            ->orderBy('sort_order', 'ASC')
            ->orderBy('name', 'ASC')
            ->get();

        $catalog = $this->permissionCatalog();
        $allowedKeys = [];
        foreach ($catalog as $permissions) {
            foreach ($permissions as $permission) {
                $allowedKeys[(string) ($permission['key'] ?? '')] = true;
            }
        }

        $submitted = $request->input('permissions', []);
        if (!is_array($submitted)) {
            $submitted = [];
        }

        foreach ($roles as $role) {
            $roleId = (int) ($role->id ?? 0);
            if ($roleId <= 0) {
                continue;
            }

            RolePermission::query()->where('role_id', $roleId)->delete();

            $rolePermissions = $submitted[$roleId] ?? [];
            if (!is_array($rolePermissions)) {
                continue;
            }

            foreach ($rolePermissions as $permissionKey => $value) {
                $permissionKey = (string) $permissionKey;
                if (!isset($allowedKeys[$permissionKey])) {
                    continue;
                }

                RolePermission::create([
                    'role_id' => $roleId,
                    'permission_key' => $permissionKey,
                    'is_allowed' => 1,
                ]);
            }
        }

        flash((string) __('roles.flash.matrix_saved'), 'success', (string) __('roles.flash.success_title'));
        return redirect('/roles/matrix');
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

    public function toggleStatus(string $role): Response
    {
        $record = Role::query()->where('slug', $role)->first();
        if (!$record instanceof Role) {
            flash((string) __('roles.flash.not_found'), 'error', (string) __('roles.flash.error_title'));
            return back();
        }

        $request = app(Request::class);
        $nextStatus = $request->boolean('is_active', false) ? 1 : 0;

        $record->update([
            'is_active' => $nextStatus,
        ]);

        flash((string) __('roles.flash.status_updated'), 'success', (string) __('roles.flash.success_title'));
        return back();
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
            bodyHtml: $bodyHtml
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
                <a href="/roles/matrix" class="btn btn-outline-primary">{$matrix}</a>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-new-role">{$new}</button>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- END PAGE HEADER -->
HTML;
    }

    private function matrixHeaderHtml(): string
    {
        $pretitle = $this->e(__('roles.pretitle'));
        $title = $this->e(__('roles.matrix.title'));
        $subtitle = $this->e(__('roles.matrix.subtitle'));
        $back = $this->e(__('roles.actions.back_to_list'));

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
              <a class="btn btn-outline-primary" href="/roles">{$back}</a>
            </div>
          </div>
        </div>
      </div>
      <!-- END PAGE HEADER -->
HTML;
    }

    private function matrixBodyHtml(): string
    {
        $roles = Role::query()
            ->select('id', 'name', 'slug', 'is_active')
            ->orderBy('sort_order', 'ASC')
            ->orderBy('name', 'ASC')
            ->get();
        $catalog = $this->permissionCatalog();
        $permissionMap = $this->loadPermissionsMap();

        $save = $this->e(__('roles.actions.save'));
        $permissionTh = $this->e(__('roles.matrix.permission'));
        $help = $this->e(__('roles.matrix.help'));
        $empty = $this->e(__('roles.matrix.empty'));
        $csrf = $this->csrfToken();

        $roleHeaders = '';
        foreach ($roles as $role) {
            $roleName = $this->e((string) ($role->name ?? 'Role'));
            $roleHeaders .= '<th class="text-center">' . $roleName . '</th>';
        }

        $accordionItems = '';
        $groupIndex = 0;
        foreach ($catalog as $group => $permissions) {
            $groupLabel = $this->e($group);
            $rows = '';
            foreach ($permissions as $permission) {
                $permissionKey = (string) ($permission['key'] ?? '');
                $permissionLabel = $this->e((string) ($permission['label'] ?? $permissionKey));
                $permissionDesc = $this->e((string) ($permission['description'] ?? ''));

                $cells = '';
                foreach ($roles as $role) {
                    $roleId = (int) ($role->id ?? 0);
                    $checked = !empty($permissionMap[$roleId][$permissionKey]) ? ' checked' : '';
                    $disabled = (int) ($role->is_active ?? 0) === 1 ? '' : ' disabled';
                    $aria = $this->e((string) ($role->name ?? 'Role') . ' - ' . $permissionLabel);
                    $name = 'permissions[' . $roleId . '][' . $permissionKey . ']';
                    $cells .= <<<HTML
<td class="text-center">
  <label class="form-check form-check-single m-0 d-inline-flex">
    <input class="form-check-input" type="checkbox" name="{$name}" value="1"{$checked}{$disabled} aria-label="{$aria}">
  </label>
</td>
HTML;
                }

                $rows .= <<<HTML
                      <tr>
                        <td>
                          <div>{$permissionLabel}</div>
                          <div class="text-secondary small">{$permissionDesc}</div>
                        </td>
                        {$cells}
                      </tr>
HTML;
            }

            if ($rows === '') {
                $colspan = 1 + max(count($roles), 1);
                $rows = <<<HTML
                      <tr>
                        <td colspan="{$colspan}" class="text-center text-secondary py-4">{$empty}</td>
                      </tr>
HTML;
            }

            $collapseId = 'perm-group-' . $groupIndex;
            $headingId = 'perm-heading-' . $groupIndex;
            $isFirst = $groupIndex === 0;
            $collapseClass = $isFirst ? 'accordion-collapse collapse show' : 'accordion-collapse collapse';
            $buttonClass = $isFirst ? 'accordion-button' : 'accordion-button collapsed';
            $expanded = $isFirst ? 'true' : 'false';
            $permCount = count($permissions);
            $permCountBadge = $this->e((string) $permCount);

            $accordionItems .= <<<HTML
                  <div class="accordion-item">
                    <h2 class="accordion-header" id="{$headingId}">
                      <button class="{$buttonClass}" type="button" data-bs-toggle="collapse" data-bs-target="#{$collapseId}" aria-expanded="{$expanded}" aria-controls="{$collapseId}">
                        <span>{$groupLabel}</span>
                        <span class="badge bg-blue-lt ms-2">{$permCountBadge}</span>
                      </button>
                    </h2>
                    <div id="{$collapseId}" class="{$collapseClass}" aria-labelledby="{$headingId}" data-bs-parent="#roles-permission-accordion">
                      <div class="accordion-body p-0">
                        <div class="table-responsive">
                          <table class="table table-vcenter card-table mb-0">
                            <thead>
                              <tr>
                                <th>{$permissionTh}</th>
                                {$roleHeaders}
                              </tr>
                            </thead>
                            <tbody>
{$rows}
                            </tbody>
                          </table>
                        </div>
                      </div>
                    </div>
                  </div>
HTML;

            $groupIndex++;
        }

        if ($accordionItems === '') {
            $accordionItems = <<<HTML
                  <div class="p-4 text-secondary">{$empty}</div>
HTML;
        }

        return <<<HTML
      <!-- BEGIN PAGE BODY -->
      <div class="page-body">
        <div class="container-xl">
          <div class="row row-cards">
            <div class="col-12">
              <div class="card">
                <div class="card-header">
                  <h3 class="card-title">{$this->e(__('roles.matrix.card_title'))}</h3>
                </div>
                <form method="POST" action="/roles/matrix">
                  <input type="hidden" name="_token" value="{$csrf}">
                  <div class="card-body border-bottom py-3 text-secondary">{$help}</div>
                  <div class="card-body">
                    <div class="accordion" id="roles-permission-accordion">
{$accordionItems}
                    </div>
                  </div>
                  <div class="card-footer d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">{$save}</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- END PAGE BODY -->
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
            $userCount = (int) ($role->user_count ?? 0);
            $updated = $this->e($this->formatDateTime($role->updated_at ?? null));
            $slug = rawurlencode((string) ($role->slug ?? $role->name ?? ''));
            $isActive = (int) ($role->is_active ?? 0) === 1;
            $statusLabel = $isActive ? $this->e($active) : $this->e($passive);
            $statusDetail = $isActive
                ? $this->e((string) __('roles.status.active_detail', ['count' => (string) $userCount]))
                : $this->e((string) __('roles.status.passive_detail'));
            $switchChecked = $isActive ? ' checked' : '';
            $csrf = $this->csrfToken();
            $switch = <<<HTML
<form method="POST" action="/roles/{$slug}/status" class="m-0">
  <input type="hidden" name="_method" value="PUT">
  <input type="hidden" name="_token" value="{$csrf}">
  <div class="form-check form-switch m-0">
    <input class="form-check-input" type="checkbox" role="switch" name="is_active" value="1"{$switchChecked} aria-label="{$statusLabel}" onchange="this.form.submit()">
  </div>
</form>
HTML;

            $rows .= <<<HTML
                      <tr>
                        <td><strong>{$name}</strong></td>
                        <td>{$users}</td>
                        <td>{$updated}</td>
                        <td>
                          {$switch}
                          <div class="text-secondary small mt-1">{$statusDetail}</div>
                        </td>
                        <td class="text-end">
                          <div class="btn-list justify-content-end flex-nowrap">
                            <a class="btn btn-outline-primary btn-sm" href="/roles/{$slug}/edit">
                              {$editLabel}
                            </a>
                            <a class="btn btn-outline-teal btn-sm" href="/roles/{$slug}">
                              {$permissionsLabel}
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
        $fieldStatus = $this->e(__('roles.form.status'));
        $cancel = $this->e(__('roles.actions.cancel'));
        $create = $this->e(__('roles.actions.create'));
        $csrf = $this->csrfToken();

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
              <form method="POST" action="/roles">
                <input type="hidden" name="_token" value="{$csrf}">
                <div class="modal-header">
                  <h5 class="modal-title">{$modalTitle}</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <div class="mb-3">
                    <label class="form-label">{$fieldName}</label>
                    <input type="text" class="form-control" name="name" required>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">{$fieldSlug}</label>
                    <input type="text" class="form-control" name="slug" placeholder="ornek: manager">
                  </div>
                  <div class="mb-3">
                    <label class="form-label">{$fieldDesc}</label>
                    <textarea class="form-control" rows="3" name="description"></textarea>
                  </div>
                  <div class="mb-0">
                    <label class="form-label d-block">{$fieldStatus}</label>
                    <label class="form-check form-switch m-0">
                      <input class="form-check-input" type="checkbox" name="is_active" value="1" checked>
                      <span class="form-check-label">{$active}</span>
                    </label>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">{$cancel}</button>
                  <button type="submit" class="btn btn-primary ms-auto">{$create}</button>
                </div>
              </form>
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

    /**
     * @return array<string,array<int,array{key:string,label:string,description:string}>>
     */
    private function permissionCatalog(): array
    {
        return [
            (string) __('roles.matrix.groups.dashboard') => [
                ['key' => 'dashboard.view', 'label' => 'dashboard.view', 'description' => (string) __('roles.matrix.descriptions.dashboard_view')],
            ],
            (string) __('roles.matrix.groups.users') => [
                ['key' => 'users.view', 'label' => 'users.view', 'description' => (string) __('roles.matrix.descriptions.users_view')],
                ['key' => 'users.create', 'label' => 'users.create', 'description' => (string) __('roles.matrix.descriptions.users_create')],
                ['key' => 'users.update', 'label' => 'users.update', 'description' => (string) __('roles.matrix.descriptions.users_update')],
                ['key' => 'users.toggle', 'label' => 'users.toggle', 'description' => (string) __('roles.matrix.descriptions.users_toggle')],
            ],
            (string) __('roles.matrix.groups.roles') => [
                ['key' => 'roles.view', 'label' => 'roles.view', 'description' => (string) __('roles.matrix.descriptions.roles_view')],
                ['key' => 'roles.create', 'label' => 'roles.create', 'description' => (string) __('roles.matrix.descriptions.roles_create')],
                ['key' => 'roles.update', 'label' => 'roles.update', 'description' => (string) __('roles.matrix.descriptions.roles_update')],
                ['key' => 'roles.toggle', 'label' => 'roles.toggle', 'description' => (string) __('roles.matrix.descriptions.roles_toggle')],
                ['key' => 'roles.matrix', 'label' => 'roles.matrix', 'description' => (string) __('roles.matrix.descriptions.roles_matrix')],
            ],
            (string) __('roles.matrix.groups.locales') => [
                ['key' => 'locales.view', 'label' => 'locales.view', 'description' => (string) __('roles.matrix.descriptions.locales_view')],
                ['key' => 'locales.update', 'label' => 'locales.update', 'description' => (string) __('roles.matrix.descriptions.locales_update')],
            ],
        ];
    }

    /**
     * @return array<int,array<string,bool>>
     */
    private function loadPermissionsMap(): array
    {
        $items = RolePermission::query()
            ->select('role_id', 'permission_key', 'is_allowed')
            ->get();

        $map = [];
        foreach ($items as $item) {
            $roleId = (int) ($item->role_id ?? 0);
            $key = (string) ($item->permission_key ?? '');
            if ($roleId <= 0 || $key === '') {
                continue;
            }
            $map[$roleId][$key] = (int) ($item->is_allowed ?? 0) === 1;
        }

        return $map;
    }

    private function slugify(string $value): string
    {
        $slug = strtolower(trim($value));
        $slug = preg_replace('/[^a-z0-9]+/i', '-', $slug) ?? '';
        $slug = trim($slug, '-');
        return substr($slug, 0, 120);
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
