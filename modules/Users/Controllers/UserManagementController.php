<?php

declare(strict_types=1);

namespace Modules\Users\Controllers;

use Core\Auth\DashboardShellRenderer;
use Core\Auth\Facades\Auth;
use Core\Http\Request;
use Core\Http\Response;
use Modules\Roles\Models\Role;
use Modules\Users\Models\User;

final class UserManagementController
{
    public function index(): Response
    {
        return $this->renderPage(
            title: (string) __('users.meta_title'),
            headerHtml: $this->indexHeaderHtml(),
            bodyHtml: $this->indexBodyHtml()
        );
    }

    public function show(string $id): Response
    {
        $user = User::query()->where('id', (int) $id)->first();
        if ($user === null) {
            return Response::make('Kullanici bulunamadi.', 404, ['Content-Type' => 'text/plain; charset=utf-8']);
        }

        return $this->renderPage(
            title: (string) __('users.detail.meta_title', ['name' => (string) ($user->name ?? '')]),
            headerHtml: $this->detailHeaderHtml((string) ($user->name ?? ''), false),
            bodyHtml: $this->detailBodyHtml($user, false)
        );
    }

    public function store(): Response
    {
        $request = app(Request::class);
        $rolesMap = $this->activeRolesMap();
        $name = trim((string) $request->input('name', ''));
        $email = trim((string) $request->input('email', ''));
        $password = (string) $request->input('password', '');
        $locale = trim((string) $request->input('locale', 'tr'));
        $isActive = $request->boolean('is_active', true) ? 1 : 0;
        $roleId = (int) $request->input('role_id', 0);
        $roleId = array_key_exists($roleId, $rolesMap) ? $roleId : 0;

        if ($name === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($password) < 6 || $roleId <= 0) {
            flash((string) __('users.flash.create_validation_failed'), 'warning', (string) __('users.flash.warning_title'));
            return redirect('/users');
        }

        $exists = User::query()
            ->where('email', $email)
            ->exists();

        if ($exists) {
            flash((string) __('users.flash.email_taken'), 'warning', (string) __('users.flash.warning_title'));
            return redirect('/users');
        }

        User::create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'locale' => $locale !== '' ? $locale : 'tr',
            'role_id' => $roleId,
            'is_active' => $isActive,
        ]);

        flash((string) __('users.flash.created'), 'success', (string) __('users.flash.success_title'));
        return redirect('/users');
    }

    public function edit(string $id): Response
    {
        $user = User::query()->where('id', (int) $id)->first();
        if ($user === null) {
            return Response::make('Kullanici bulunamadi.', 404, ['Content-Type' => 'text/plain; charset=utf-8']);
        }

        return $this->renderPage(
            title: (string) __('users.edit.meta_title', ['name' => (string) ($user->name ?? '')]),
            headerHtml: $this->detailHeaderHtml((string) ($user->name ?? ''), true),
            bodyHtml: $this->detailBodyHtml($user, true)
        );
    }

    public function toggleStatus(string $id): Response
    {
        $user = User::query()->where('id', (int) $id)->first();
        if (!$user instanceof User) {
            flash((string) __('users.flash.not_found'), 'error', (string) __('users.flash.error_title'));
            return back();
        }

        $request = app(Request::class);
        $nextStatus = $request->boolean('is_active', false) ? 1 : 0;

        $user->update([
            'is_active' => $nextStatus,
        ]);

        flash((string) __('users.flash.status_updated'), 'success', (string) __('users.flash.success_title'));
        return back();
    }

    public function update(string $id): Response
    {
        $user = User::query()->where('id', (int) $id)->first();
        if (!$user instanceof User) {
            flash((string) __('users.flash.not_found'), 'error', (string) __('users.flash.error_title'));
            return redirect('/users');
        }

        $request = app(Request::class);
        $rolesMap = $this->activeRolesMap();
        $name = trim((string) $request->input('name', ''));
        $email = trim((string) $request->input('email', ''));
        $locale = trim((string) $request->input('locale', 'tr'));
        $isActive = $request->boolean('is_active', false) ? 1 : 0;
        $roleId = (int) $request->input('role_id', 0);
        $roleId = array_key_exists($roleId, $rolesMap) ? $roleId : 0;
        $lockPin = trim((string) $request->input('lock_pin', ''));
        $lockPinConfirmation = trim((string) $request->input('lock_pin_confirmation', ''));
        $resetLockPin = $request->boolean('lock_pin_reset', false);

        if ($name === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $roleId <= 0) {
            flash((string) __('users.flash.validation_failed'), 'warning', (string) __('users.flash.warning_title'));
            return redirect('/users/' . (int) $user->id . '/edit');
        }

        if (($lockPin !== '' || $lockPinConfirmation !== '') && !preg_match('/^\d{4,8}$/', $lockPin)) {
            flash((string) __('users.flash.lock_pin_invalid'), 'warning', (string) __('users.flash.warning_title'));
            return redirect('/users/' . (int) $user->id . '/edit');
        }

        if (($lockPin !== '' || $lockPinConfirmation !== '') && $lockPin !== $lockPinConfirmation) {
            flash((string) __('users.flash.lock_pin_mismatch'), 'warning', (string) __('users.flash.warning_title'));
            return redirect('/users/' . (int) $user->id . '/edit');
        }

        $exists = User::query()
            ->where('email', $email)
            ->where('id', '!=', (int) $user->id)
            ->exists();

        if ($exists) {
            flash((string) __('users.flash.email_taken'), 'warning', (string) __('users.flash.warning_title'));
            return redirect('/users/' . (int) $user->id . '/edit');
        }

        $updates = [
            'name' => $name,
            'email' => $email,
            'locale' => $locale,
            'role_id' => $roleId,
            'is_active' => $isActive,
        ];

        if ($resetLockPin) {
            $updates['lock_pin_hash'] = null;
        } elseif ($lockPin !== '') {
            $updates['lock_pin_hash'] = password_hash($lockPin, PASSWORD_ARGON2ID);
        }

        $user->update($updates);

        flash((string) __('users.flash.updated'), 'success', (string) __('users.flash.success_title'));
        return redirect('/users');
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
            return Response::make('Users template bulunamadi.', 500, ['Content-Type' => 'text/plain; charset=utf-8']);
        }

        return Response::make($html, 200, ['Content-Type' => 'text/html; charset=utf-8']);
    }

    private function indexHeaderHtml(): string
    {
        $pretitle = $this->e(__('users.pretitle'));
        $title = $this->e(__('users.title'));
        $subtitle = $this->e(__('users.subtitle'));
        $new = $this->e(__('users.actions.new'));

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
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-new-user">{$new}</button>
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

        $active = (string) __('users.status.active');
        $passive = (string) __('users.status.passive');
        $editLabel = $this->e(__('users.actions.edit'));
        $detailLabel = $this->e(__('users.actions.detail'));

        $usersQuery = User::query()
            ->select('id', 'name', 'email', 'is_active', 'last_login_at', 'updated_at')
            ->orderBy('id', 'DESC');

        if ($statusFilter === 'active') {
            $usersQuery->where('is_active', 1);
        } elseif ($statusFilter === 'passive') {
            $usersQuery->where('is_active', 0);
        }

        if ($searchQuery !== '') {
            $like = '%' . $searchQuery . '%';
            $usersQuery->where(static function ($query) use ($like): void {
                $query->where('name', 'LIKE', $like)
                    ->orWhere('email', 'LIKE', $like);
            });
        }

        $users = $usersQuery->get();

        $rows = '';
        foreach ($users as $user) {
            $id = (int) ($user->id ?? 0);
            $name = $this->e((string) ($user->name ?? '-'));
            $email = $this->e((string) ($user->email ?? '-'));
            $lastLogin = $this->e($this->formatDateTime($user->last_login_at ?? null));
            $lastLoginRaw = $this->formatDateTime($user->last_login_at ?? null);
            $updated = $this->e($this->formatDateTime($user->updated_at ?? null));
            $isActive = (int) ($user->is_active ?? 0) === 1;
            $statusLabel = $isActive ? $this->e($active) : $this->e($passive);
            $statusDetail = !$isActive
                ? $this->e((string) __('users.status.passive_detail'))
                : ($lastLoginRaw === '-'
                    ? $this->e((string) __('users.status.active_detail_never'))
                    : $this->e((string) __('users.status.active_detail_last_login', ['date' => $lastLoginRaw])));
            $switchChecked = $isActive ? ' checked' : '';
            $csrf = $this->csrfToken();
            $switch = <<<HTML
<form method="POST" action="/users/{$id}/status" class="m-0">
  <input type="hidden" name="_method" value="PUT">
  <input type="hidden" name="_token" value="{$csrf}">
  <div class="form-check form-switch m-0">
    <input class="form-check-input" type="checkbox" role="switch" name="is_active" value="1"{$switchChecked} aria-label="{$statusLabel}" onchange="this.form.submit()">
  </div>
</form>
HTML;

            $rows .= <<<HTML
                      <tr>
                        <td>{$name}</td>
                        <td>{$email}</td>
                        <td>{$lastLogin}</td>
                        <td>{$updated}</td>
                        <td>
                          {$switch}
                          <div class="text-secondary small mt-1">{$statusDetail}</div>
                        </td>
                        <td class="text-end">
                          <div class="btn-list justify-content-end flex-nowrap">
                            <a class="btn btn-outline-primary btn-sm" href="/users/{$id}/edit">
                              {$editLabel}
                            </a>
                            <a class="btn btn-outline-teal btn-sm" href="/users/{$id}">
                              {$detailLabel}
                            </a>
                          </div>
                        </td>
                      </tr>
HTML;
        }

        if ($rows === '') {
            $emptyText = $this->e(__('users.table.empty'));
            $rows = <<<HTML
                      <tr>
                        <td colspan="6" class="text-secondary text-center py-4">{$emptyText}</td>
                      </tr>
HTML;
        }

        $tableTitle = $this->e(__('users.table.title'));
        $search = $this->e(__('users.filters.search'));
        $all = $this->e(__('users.filters.all'));
        $nameTh = $this->e(__('users.table.name'));
        $emailTh = $this->e(__('users.table.email'));
        $lastLoginTh = $this->e(__('users.table.last_login_at'));
        $updatedTh = $this->e(__('users.table.updated_at'));
        $statusTh = $this->e(__('users.table.status'));
        $summaryTitle = $this->e(__('users.side.title'));
        $summaryText = $this->e(__('users.side.description'));
        $summaryHint = $this->e(__('users.side.hint'));
        $modalTitle = $this->e(__('users.modal.new_title'));
        $fieldName = $this->e(__('users.form.name'));
        $fieldEmail = $this->e(__('users.form.email'));
        $fieldPassword = $this->e(__('users.form.password'));
        $fieldRole = $this->e(__('users.form.role'));
        $fieldLocale = $this->e(__('users.form.locale'));
        $fieldStatus = $this->e(__('users.form.status'));
        $cancel = $this->e(__('users.actions.cancel'));
        $create = $this->e(__('users.actions.create'));
        $csrf = $this->csrfToken();
        $activeChecked = ' checked';
        $rolesOptions = $this->roleOptionsHtml();

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
                  <form method="GET" action="/users" class="d-flex gap-2">
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
                        <th>{$nameTh}</th>
                        <th>{$emailTh}</th>
                        <th>{$lastLoginTh}</th>
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

        <div class="modal modal-blur fade" id="modal-new-user" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <form method="POST" action="/users">
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
                    <label class="form-label">{$fieldEmail}</label>
                    <input type="email" class="form-control" name="email" required>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">{$fieldPassword}</label>
                    <input type="password" class="form-control" name="password" minlength="6" required>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">{$fieldRole}</label>
                    <select class="form-select" name="role_id" required>
{$rolesOptions}
                    </select>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">{$fieldLocale}</label>
                    <select class="form-select" name="locale">
                      <option value="tr">tr</option>
                      <option value="en">en</option>
                    </select>
                  </div>
                  <div class="mb-0">
                    <label class="form-label d-block">{$fieldStatus}</label>
                    <label class="form-check form-switch m-0">
                      <input class="form-check-input" type="checkbox" name="is_active" value="1"{$activeChecked}>
                      <span class="form-check-label">{$this->e($active)}</span>
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

    private function detailHeaderHtml(string $name, bool $edit): string
    {
        $pretitle = $this->e(__('users.pretitle'));
        $title = $edit ? $this->e(__('users.edit.title')) : $this->e(__('users.detail.title'));
        $subtitle = $this->e(__('users.detail.subtitle', ['name' => $name]));
        $back = $this->e(__('users.actions.back_to_list'));

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
              <a class="btn btn-outline-primary" href="/users">{$back}</a>
            </div>
          </div>
        </div>
      </div>
      <!-- END PAGE HEADER -->
HTML;
    }

    private function detailBodyHtml(User $user, bool $edit): string
    {
        $id = (int) ($user->id ?? 0);
        $name = $this->e((string) ($user->name ?? ''));
        $email = $this->e((string) ($user->email ?? ''));
        $locale = $this->e((string) ($user->locale ?? 'tr'));
        $isActive = (int) ($user->is_active ?? 0) === 1;
        $active = $this->e(__('users.status.active'));
        $passive = $this->e(__('users.status.passive'));

        $fieldName = $this->e(__('users.form.name'));
        $fieldEmail = $this->e(__('users.form.email'));
        $fieldRole = $this->e(__('users.form.role'));
        $fieldLocale = $this->e(__('users.form.locale'));
        $fieldStatus = $this->e(__('users.form.status'));
        $fieldLockPin = $this->e(__('users.form.lock_pin'));
        $fieldLockPinConfirmation = $this->e(__('users.form.lock_pin_confirmation'));
        $fieldLockPinHint = $this->e(__('users.form.lock_pin_hint'));
        $fieldLockPinReset = $this->e(__('users.form.lock_pin_reset'));
        $fieldLastLogin = $this->e(__('users.table.last_login_at'));
        $fieldUpdated = $this->e(__('users.table.updated_at'));

        $save = $this->e(__('users.actions.save'));
        $editLabel = $this->e(__('users.actions.edit'));
        $csrf = $this->csrfToken();
        $checked = $isActive ? ' checked' : '';
        $selectedRoleId = (int) ($user->role_id ?? 0);
        $roleName = $this->e($this->findRoleName($selectedRoleId));
        $rolesOptions = $this->roleOptionsHtml($selectedRoleId);

        $left = $edit
            ? <<<HTML
                <div class="card">
                  <div class="card-body">
                    <form method="POST" action="/users/{$id}">
                      <input type="hidden" name="_method" value="PUT">
                      <input type="hidden" name="_token" value="{$csrf}">
                      <div class="mb-3">
                        <label class="form-label">{$fieldName}</label>
                        <input type="text" class="form-control" name="name" value="{$name}" required>
                      </div>
                      <div class="mb-3">
                        <label class="form-label">{$fieldEmail}</label>
                        <input type="email" class="form-control" name="email" value="{$email}" required>
                      </div>
                      <div class="mb-3">
                        <label class="form-label">{$fieldRole}</label>
                        <select class="form-select" name="role_id" required>
{$rolesOptions}
                        </select>
                      </div>
                      <div class="mb-3">
                        <label class="form-label">{$fieldLocale}</label>
                        <select class="form-select" name="locale">
                          <option value="tr"{$this->selectedAttr($locale, 'tr')}>tr</option>
                          <option value="en"{$this->selectedAttr($locale, 'en')}>en</option>
                        </select>
                      </div>
                      <div class="mb-3">
                        <label class="form-label">{$fieldLockPin}</label>
                        <input type="password" class="form-control" name="lock_pin" inputmode="numeric" pattern="[0-9]{4,8}" maxlength="8" autocomplete="new-password">
                        <div class="form-hint">{$fieldLockPinHint}</div>
                      </div>
                      <div class="mb-3">
                        <label class="form-label">{$fieldLockPinConfirmation}</label>
                        <input type="password" class="form-control" name="lock_pin_confirmation" inputmode="numeric" pattern="[0-9]{4,8}" maxlength="8" autocomplete="new-password">
                      </div>
                      <div class="mb-3">
                        <label class="form-check">
                          <input class="form-check-input" type="checkbox" name="lock_pin_reset" value="1">
                          <span class="form-check-label">{$fieldLockPinReset}</span>
                        </label>
                      </div>
                      <div class="mb-4">
                        <label class="form-label d-block">{$fieldStatus}</label>
                        <label class="form-check form-switch m-0">
                          <input class="form-check-input" type="checkbox" name="is_active" value="1"{$checked}>
                          <span class="form-check-label">{$active}</span>
                        </label>
                      </div>
                      <button class="btn btn-primary">{$save}</button>
                    </form>
                  </div>
                </div>
HTML
            : <<<HTML
                <div class="card">
                  <div class="card-body">
                    <dl class="row mb-0">
                      <dt class="col-4">{$fieldName}</dt><dd class="col-8">{$name}</dd>
                      <dt class="col-4">{$fieldEmail}</dt><dd class="col-8">{$email}</dd>
                      <dt class="col-4">{$fieldRole}</dt><dd class="col-8">{$roleName}</dd>
                      <dt class="col-4">{$fieldLocale}</dt><dd class="col-8">{$locale}</dd>
                      <dt class="col-4">{$fieldStatus}</dt><dd class="col-8"><span class="badge bg-{$this->statusColor($isActive)}-lt">{$this->statusText($isActive, $active, $passive)}</span></dd>
                    </dl>
                    <div class="mt-4">
                      <a class="btn btn-outline-primary" href="/users/{$id}/edit">{$editLabel}</a>
                    </div>
                  </div>
                </div>
HTML;

        $lastLogin = $this->e($this->formatDateTime($user->last_login_at ?? null));
        $updated = $this->e($this->formatDateTime($user->updated_at ?? null));
        $infoTitle = $this->e(__('users.detail.info_title'));

        $right = <<<HTML
            <div class="card">
              <div class="card-header"><h3 class="card-title">{$infoTitle}</h3></div>
              <div class="card-body">
                <dl class="row mb-0">
                  <dt class="col-5">{$fieldLastLogin}</dt><dd class="col-7">{$lastLogin}</dd>
                  <dt class="col-5">{$fieldUpdated}</dt><dd class="col-7">{$updated}</dd>
                </dl>
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

    private function statusText(bool $isActive, string $active, string $passive): string
    {
        return $isActive ? $active : $passive;
    }

    private function statusColor(bool $isActive): string
    {
        return $isActive ? 'green' : 'yellow';
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

    /**
     * @return array<int,string>
     */
    private function activeRolesMap(): array
    {
        $roles = Role::query()
            ->select('id', 'name')
            ->where('is_active', 1)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('name', 'ASC')
            ->get();

        $map = [];
        foreach ($roles as $role) {
            $id = (int) ($role->id ?? 0);
            if ($id <= 0) {
                continue;
            }
            $map[$id] = (string) ($role->name ?? 'Role');
        }

        return $map;
    }

    private function roleOptionsHtml(int $selectedRoleId = 0): string
    {
        $roles = $this->activeRolesMap();
        $placeholder = $this->e((string) __('users.form.role_placeholder'));
        $html = '                          <option value="">' . $placeholder . '</option>' . PHP_EOL;

        foreach ($roles as $id => $name) {
            $selected = $id === $selectedRoleId ? ' selected' : '';
            $html .= '                          <option value="' . $id . '"' . $selected . '>' . $this->e($name) . '</option>' . PHP_EOL;
        }

        return rtrim($html);
    }

    private function findRoleName(int $roleId): string
    {
        if ($roleId <= 0) {
            return (string) __('users.form.role_unassigned');
        }

        $role = Role::query()->select('name')->where('id', $roleId)->first();
        if ($role === null) {
            return (string) __('users.form.role_unassigned');
        }

        return (string) ($role->name ?? __('users.form.role_unassigned'));
    }
}
