<?php

declare(strict_types=1);

namespace Core\Auth;

use Core\Auth\Facades\Auth;
use Core\Database\DatabaseManager;
use Core\Database\Exceptions\DatabaseException;
use Core\Http\Request;
use Core\Http\Response;
use Core\Logging\Logger;
use Core\Mail\PasswordResetMail;
use Modules\Users\Models\User;

class WebAuthController
{
    public function showLogin(Request $request): Response
    {
        $csrfToken = $this->csrfToken();

        $error = trim((string) $request->get('error', ''));
        $success = trim((string) $request->get('success', ''));
        $html = $this->renderTemplate($this->line('auth.web.login.meta_title'), $this->loginBody($error, $success, $csrfToken), false);

        return Response::make($html, 200, ['Content-Type' => 'text/html; charset=utf-8']);
    }

    public function login(Request $request): Response
    {
        $email = trim((string) $request->input('email', ''));
        $password = (string) $request->input('password', '');
        $remember = (bool) $request->boolean('remember', false);

        if ($email === '' || $password === '') {
            app(Logger::class)->channel('auth')->warning('auth.login.validation_failed', [
                'email' => $email,
                'reason' => 'missing_credentials',
            ]);
            return Response::redirect('/login?error=' . $this->lineForUrl('auth.web.login.error_required'));
        }

        $ok = Auth::guard('session')->attempt([
            'email' => $email,
            'password' => $password,
        ], $remember);

        if (!$ok) {
            app(Logger::class)->channel('security')->warning('auth.login.failed', [
                'email' => $email,
            ]);
            return Response::redirect('/login?error=' . $this->lineForUrl('auth.web.login.error_invalid'));
        }

        app(Logger::class)->channel('auth')->info('auth.login.success', [
            'email' => $email,
        ]);
        return Response::redirect('/dashboard');
    }

    public function showForgotPassword(Request $request): Response
    {
        $error = trim((string) $request->get('error', ''));
        $success = trim((string) $request->get('success', ''));
        $token = trim((string) $request->get('token', ''));
        $step = trim((string) $request->get('step', 'verify'));
        $csrfToken = $this->csrfToken();

        $errorHtml = $error !== ''
            ? '<div class="alert alert-danger" role="alert">' . htmlspecialchars($error, ENT_QUOTES, 'UTF-8') . '</div>'
            : '';
        $successHtml = $success !== ''
            ? '<div class="alert alert-success" role="alert">' . htmlspecialchars($success, ENT_QUOTES, 'UTF-8') . '</div>'
            : '';
        $resolvedToken = $token !== '' ? $token : ($step === 'reset' ? trim((string) $request->input('token', '')) : '');
        $tokenRow = $resolvedToken !== '' ? $this->findValidPasswordResetToken($resolvedToken) : null;
        $isResetStep = $tokenRow !== null;

        if (($step === 'reset' || $resolvedToken !== '') && !$isResetStep) {
            return Response::redirect('/forgot-password?error=' . $this->lineForUrl('auth.web.forgot.error_session_expired'));
        }

        $description = $isResetStep
            ? $this->tr('auth.web.forgot.description_set_new')
            : $this->tr('auth.web.forgot.description_verify');
        $title = $isResetStep
            ? $this->tr('auth.web.forgot.title_set_new')
            : $this->tr('auth.web.forgot.title');
        $formAction = $isResetStep ? '/forgot-password/reset' : '/forgot-password';
        $submitLabel = $isResetStep
            ? $this->tr('auth.web.forgot.submit_set')
            : $this->tr('auth.web.forgot.submit_verify');

        $formBody = $isResetStep
            ? <<<HTML
          <input type="hidden" name="token" value="{$this->escape($resolvedToken)}">
          <div class="mb-3">
            <label class="form-label">{$this->tr('auth.web.fields.password')}</label>
            <input class="form-control" type="password" name="password" minlength="6" required>
          </div>
          <div class="mb-3">
            <label class="form-label">{$this->tr('auth.web.forgot.password_confirmation')}</label>
            <input class="form-control" type="password" name="password_confirmation" minlength="6" required>
          </div>
HTML
            : <<<HTML
          <div class="mb-3">
            <label class="form-label">{$this->tr('auth.web.fields.email')}</label>
            <input class="form-control" type="email" name="email" required>
          </div>
HTML;
        $appName = $this->appName();
        $brandLogo = $this->brandLogoHtml('kirpi-brand-logo mx-auto mb-2');
        $copyright = $this->copyrightHtml('text-center text-secondary small mt-auto mb-4 px-3');

        $html = $this->renderTemplate($this->line('auth.web.forgot.meta_title'), <<<HTML
<div class="container container-tight py-5 min-vh-100 d-flex flex-column">
  <div class="my-auto">
    <div class="text-center mb-4">
      {$brandLogo}
      <div class="text-uppercase text-secondary small fw-semibold mt-2">{$appName}</div>
      <h1 class="h3 mt-2 mb-0">{$title}</h1>
    </div>
    <div class="card card-md">
      <div class="card-body">
        <p class="text-secondary">{$description}</p>
        {$successHtml}
        {$errorHtml}
        <form method="post" action="{$formAction}" autocomplete="on">
          <input type="hidden" name="_token" value="{$csrfToken}">
          {$formBody}
          <button class="btn btn-primary w-100" type="submit">{$submitLabel}</button>
        </form>
      </div>
      <div class="hr-text">{$this->tr('auth.web.common.or')}</div>
      <div class="card-body">
        <a class="btn btn-outline-secondary w-100" href="/login">{$this->tr('auth.web.common.back_to_login')}</a>
      </div>
      <div class="card-footer text-center text-secondary">
        {$this->tr('auth.web.forgot.accept_prefix')} <a href="/tos" class="link-secondary">{$this->tr('auth.web.common.terms')}</a> {$this->tr('auth.web.forgot.accept_suffix')}
      </div>
    </div>
  </div>
  {$copyright}
</div>
HTML, false);

        return Response::make($html, 200, ['Content-Type' => 'text/html; charset=utf-8']);
    }

    public function forgotPassword(Request $request): Response
    {
        $email = trim((string) $request->input('email', ''));
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return Response::redirect('/forgot-password?error=' . $this->lineForUrl('auth.web.forgot.error_invalid_email'));
        }

        /** @var User|null $target */
        $target = User::query()->where('email', $email)->first();
        if (!$target instanceof User) {
            return Response::redirect('/forgot-password?error=' . $this->lineForUrl('auth.web.forgot.error_not_found'));
        }

        try {
            $token = $this->issuePasswordResetToken((int) $target->id);
            $resetUrl = $this->passwordResetUrl($token);
            $mailable = new PasswordResetMail(
                appName: html_entity_decode($this->appName(), ENT_QUOTES, 'UTF-8'),
                resetUrl: $resetUrl,
                expiresMinutes: 30
            );
            mail_manager()->to((string) $target->email, (string) ($target->name ?? ''))->send($mailable);
            app(Logger::class)->channel('auth')->info('auth.password_reset.email_sent', [
                'user_id' => (int) $target->id,
                'email' => (string) $target->email,
            ]);
        } catch (DatabaseException) {
            return Response::redirect('/forgot-password?error=' . $this->lineForUrl('auth.web.forgot.error_setup_required'));
        } catch (\Throwable) {
            app(Logger::class)->channel('mail')->error('auth.password_reset.email_failed', [
                'user_id' => (int) $target->id,
                'email' => (string) $target->email,
            ]);
            return Response::redirect('/forgot-password?error=' . $this->lineForUrl('auth.web.forgot.error_send_failed'));
        }

        return Response::redirect('/forgot-password?success=' . $this->lineForUrl('auth.web.forgot.success_sent'));
    }

    public function resetForgotPassword(Request $request): Response
    {
        $token = trim((string) $request->input('token', ''));
        if ($token === '') {
            return Response::redirect('/forgot-password?error=' . $this->lineForUrl('auth.web.forgot.error_session_expired'));
        }
        $tokenRow = $this->findValidPasswordResetToken($token);
        if ($tokenRow === null) {
            return Response::redirect('/forgot-password?error=' . $this->lineForUrl('auth.web.forgot.error_session_expired'));
        }

        $password = (string) $request->input('password', '');
        $passwordConfirmation = (string) $request->input('password_confirmation', '');

        if (mb_strlen($password) < 6) {
            return Response::redirect('/forgot-password?token=' . rawurlencode($token) . '&error=' . $this->lineForUrl('auth.web.forgot.error_password_invalid'));
        }

        if ($password !== $passwordConfirmation) {
            return Response::redirect('/forgot-password?token=' . rawurlencode($token) . '&error=' . $this->lineForUrl('auth.web.forgot.error_password_mismatch'));
        }

        /** @var User|null $target */
        $target = User::query()->where('id', (int) $tokenRow->user_id)->first();
        if (!$target instanceof User) {
            $this->markPasswordResetUsed((int) $tokenRow->id);
            return Response::redirect('/forgot-password?error=' . $this->lineForUrl('auth.web.forgot.error_not_found'));
        }

        $target->update([
            'password' => $password,
            'lock_pin_hash' => null,
        ]);

        $this->markPasswordResetUsed((int) $tokenRow->id);
        app(Logger::class)->channel('audit')->notice('auth.password_reset.completed', [
            'user_id' => (int) $target->id,
            'pin_cleared' => true,
        ]);
        Auth::guard('session')->logout();
        return Response::redirect('/login?success=' . $this->lineForUrl('auth.web.forgot.success_login'));
    }

    public function showForgotPin(Request $request): Response
    {
        $error = trim((string) $request->get('error', ''));
        $step = trim((string) $request->get('step', 'verify'));
        $csrfToken = $this->csrfToken();

        $errorHtml = $error !== ''
            ? '<div class="alert alert-danger" role="alert">' . htmlspecialchars($error, ENT_QUOTES, 'UTF-8') . '</div>'
            : '';

        $appName = $this->appName();
        $brandLogo = $this->brandLogoHtml('kirpi-brand-logo mx-auto mb-2');
        $copyright = $this->copyrightHtml('text-center text-secondary small mt-auto mb-4 px-3');
        $isResetStep = $step === 'reset' && $this->isPinResetVerified();

        if ($step === 'reset' && !$isResetStep) {
            return Response::redirect('/forgot-pin?error=' . $this->lineForUrl('auth.web.forgot_pin.error_session_expired'));
        }

        $description = $isResetStep
            ? $this->tr('auth.web.forgot_pin.description_set_new')
            : $this->tr('auth.web.forgot_pin.description_verify');
        $title = $isResetStep
            ? $this->tr('auth.web.forgot_pin.title_set_new')
            : $this->tr('auth.web.forgot_pin.title');

        $formAction = $isResetStep ? '/forgot-pin/reset' : '/forgot-pin';
        $submitText = $isResetStep
            ? $this->tr('auth.web.forgot_pin.submit_set')
            : $this->tr('auth.web.forgot_pin.submit_verify');

        $formBody = $isResetStep
            ? <<<HTML
          <div class="mb-3">
            <label class="form-label">{$this->tr('auth.web.fields.pin')}</label>
            <input class="form-control" type="password" name="pin" inputmode="numeric" pattern="[0-9]{4,8}" maxlength="8" required>
            <div class="form-hint">{$this->tr('auth.web.forgot_pin.pin_hint')}</div>
          </div>
          <div class="mb-3">
            <label class="form-label">{$this->tr('auth.web.forgot_pin.pin_confirmation')}</label>
            <input class="form-control" type="password" name="pin_confirmation" inputmode="numeric" pattern="[0-9]{4,8}" maxlength="8" required>
          </div>
HTML
            : <<<HTML
          <div class="mb-3">
            <label class="form-label">{$this->tr('auth.web.fields.email')}</label>
            <input class="form-control" type="email" name="email" required>
          </div>
          <div class="mb-3">
            <label class="form-label">{$this->tr('auth.web.fields.password')}</label>
            <input class="form-control" type="password" name="password" required>
          </div>
HTML;

        $html = $this->renderTemplate($this->line('auth.web.forgot_pin.meta_title'), <<<HTML
<div class="container container-tight py-5 min-vh-100 d-flex flex-column">
  <div class="my-auto">
    <div class="text-center mb-4">
      {$brandLogo}
      <div class="text-uppercase text-secondary small fw-semibold mt-2">{$appName}</div>
      <h1 class="h3 mt-2 mb-0">{$title}</h1>
    </div>
    <div class="card card-md">
      <div class="card-body">
        <p class="text-secondary">{$description}</p>
        {$errorHtml}
        <form method="post" action="{$formAction}" autocomplete="on">
          <input type="hidden" name="_token" value="{$csrfToken}">
          {$formBody}
          <button class="btn btn-primary w-100" type="submit">{$submitText}</button>
        </form>
      </div>
      <div class="hr-text">{$this->tr('auth.web.common.or')}</div>
      <div class="card-body">
        <a class="btn btn-outline-secondary w-100" href="/lock">{$this->tr('auth.web.forgot_pin.back_to_lock')}</a>
        <a class="btn btn-link w-100 mt-2" href="/forgot-password">{$this->tr('auth.web.lock.forgot_password')}</a>
      </div>
    </div>
  </div>
  {$copyright}
</div>
HTML, false);

        return Response::make($html, 200, ['Content-Type' => 'text/html; charset=utf-8']);
    }

    public function forgotPin(Request $request): Response
    {
        $currentUser = Auth::guard('session')->user();
        if (!$currentUser instanceof User) {
            return Response::redirect('/login');
        }

        $email = trim((string) $request->input('email', ''));
        $password = (string) $request->input('password', '');

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return Response::redirect('/forgot-pin?error=' . $this->lineForUrl('auth.web.forgot_pin.error_invalid_email'));
        }
        if ($password === '') {
            return Response::redirect('/forgot-pin?error=' . $this->lineForUrl('auth.web.forgot_pin.error_password_required'));
        }

        /** @var User|null $target */
        $target = User::query()->where('email', $email)->first();
        if (
            !$target instanceof User
            || (int) $target->id !== (int) ($currentUser->id ?? 0)
            || !$target->verifyPassword($password)
        ) {
            return Response::redirect('/forgot-pin?error=' . $this->lineForUrl('auth.web.forgot_pin.error_invalid_credentials'));
        }

        $this->markPinResetVerified((int) $target->id);

        return Response::redirect('/forgot-pin?step=reset');
    }

    public function resetForgotPin(Request $request): Response
    {
        $currentUser = Auth::guard('session')->user();
        if (!$currentUser instanceof User) {
            return Response::redirect('/login');
        }

        $verifiedUserId = $this->pinResetVerifiedUserId();
        $currentUserId = (int) ($currentUser->id ?? 0);
        if ($verifiedUserId === null || $verifiedUserId !== $currentUserId) {
            return Response::redirect('/forgot-pin?error=' . $this->lineForUrl('auth.web.forgot_pin.error_session_expired'));
        }

        $pin = trim((string) $request->input('pin', ''));
        $pinConfirmation = trim((string) $request->input('pin_confirmation', ''));

        if (!preg_match('/^\d{4,8}$/', $pin)) {
            return Response::redirect('/forgot-pin?step=reset&error=' . $this->lineForUrl('auth.web.forgot_pin.error_pin_invalid'));
        }

        if ($pin !== $pinConfirmation) {
            return Response::redirect('/forgot-pin?step=reset&error=' . $this->lineForUrl('auth.web.forgot_pin.error_pin_mismatch'));
        }

        $currentUser->update([
            'lock_pin_hash' => password_hash($pin, PASSWORD_ARGON2ID),
        ]);

        $redirect = $this->getLockReturnPath();
        $this->clearPinResetVerified();
        $this->clearLockState();

        if (function_exists('flash')) {
            flash((string) __('auth.web.forgot_pin.success'), 'success', 'PIN');
        }

        return Response::redirect($redirect);
    }

    public function termsOfService(): Response
    {
        $appName = $this->appName();
        $brandLogo = $this->brandLogoHtml('kirpi-brand-logo mx-auto mb-2');
        $copyright = $this->copyrightHtml('text-center text-secondary small mt-auto mb-4 px-3');
        $html = $this->renderTemplate($this->line('auth.web.tos.meta_title'), <<<HTML
<div class="container container-narrow py-5 min-vh-100 d-flex flex-column">
  <div class="my-auto">
    <div class="text-center mb-4">
      {$brandLogo}
      <div class="text-uppercase text-secondary small fw-semibold mt-2">{$appName}</div>
    </div>
    <div class="card card-md">
      <div class="card-body">
        <h3 class="card-title">{$this->tr('auth.web.tos.title')}</h3>
        <div class="text-secondary">
          <p>{$this->tr('auth.web.tos.p1', ['app' => html_entity_decode($appName, ENT_QUOTES, 'UTF-8')])}</p>
          <p>{$this->tr('auth.web.tos.p2')}</p>
          <p>{$this->tr('auth.web.tos.p3')}</p>
        </div>
        <div class="mt-4">
          <a class="btn btn-primary" href="/login">{$this->tr('auth.web.common.back_to_login')}</a>
        </div>
      </div>
    </div>
  </div>
  {$copyright}
</div>
HTML);

        return Response::make($html, 200, ['Content-Type' => 'text/html; charset=utf-8']);
    }
    public function logout(): Response
    {
        $user = Auth::guard('session')->user();
        app(Logger::class)->channel('auth')->info('auth.logout', [
            'user_id' => (int) ($user?->id ?? 0),
        ]);
        Auth::guard('session')->logout();
        return Response::redirect('/login');
    }

    public function showLockScreen(Request $request): Response
    {
        if (Auth::guard('session')->guest()) {
            return Response::redirect('/login');
        }

        if ($request->boolean('lock', false)) {
            $this->lockCurrentSession((string) $request->get('return', ''));
        } elseif (!$this->isSessionLocked()) {
            $this->lockCurrentSession((string) $request->get('return', ''));
        }

        $user = Auth::guard('session')->user();
        $name = htmlspecialchars((string) ($user?->name ?? 'User'), ENT_QUOTES, 'UTF-8');
        $email = htmlspecialchars((string) ($user?->email ?? ''), ENT_QUOTES, 'UTF-8');
        $hasUserEmail = $email !== '';
        $hasPin = method_exists($user, 'hasLockPin') ? (bool) $user->hasLockPin() : false;
        $csrfToken = $this->csrfToken();
        $error = trim((string) $request->get('error', ''));
        $success = trim((string) $request->get('success', ''));
        $errorHtml = $error !== ''
            ? '<div class="alert alert-danger mb-3" role="alert">' . htmlspecialchars($error, ENT_QUOTES, 'UTF-8') . '</div>'
            : '';
        $successHtml = $success !== ''
            ? '<div class="alert alert-success mb-3" role="alert">' . htmlspecialchars($success, ENT_QUOTES, 'UTF-8') . '</div>'
            : '';
        $appName = $this->appName();
        $brandLogo = $this->brandLogoHtml('kirpi-brand-logo mx-auto mb-2');
        $copyright = $this->copyrightHtml('text-center text-secondary small mt-auto mb-4 px-3');
        $emailField = '';
        $secretLabel = $hasPin ? $this->tr('auth.web.fields.pin') : $this->tr('auth.web.fields.password');
        $secretName = $hasPin ? 'pin' : 'password';
        $secretInputMode = $hasPin ? 'numeric' : '';
        $secretPattern = $hasPin ? ' pattern="[0-9]{4,8}" maxlength="8"' : '';
        $hintHtml = $hasPin
            ? '<div class="form-hint">' . $this->tr('auth.web.lock.pin_hint') . '</div>'
            : '<div class="form-hint">' . $this->tr('auth.web.lock.password_hint') . '</div>';

        $html = $this->renderTemplate($this->line('auth.web.lock.meta_title'), <<<HTML
<div class="container container-tight py-5 min-vh-100 d-flex flex-column">
  <div class="my-auto">
    <div class="text-center mb-4">
      {$brandLogo}
      <div class="text-uppercase text-secondary small fw-semibold mb-1">{$appName}</div>
      <h2 class="h3 mb-1">{$name}</h2>
      <div class="text-secondary">{$this->tr('auth.web.lock.description')}</div>
    </div>
    <div class="card card-md">
      <div class="card-body">
        {$successHtml}
        {$errorHtml}
        <form method="post" action="/lock">
          <input type="hidden" name="_token" value="{$csrfToken}">
          {$emailField}
          <div class="mb-3">
            <label class="form-label">{$secretLabel}</label>
            <input class="form-control" type="password" name="{$secretName}" required inputmode="{$secretInputMode}"{$secretPattern}>
            {$hintHtml}
          </div>
          <button class="btn btn-primary w-100" type="submit">{$this->tr('auth.web.lock.submit')}</button>
        </form>
      </div>
      <div class="card-footer text-center">
        <a href="/forgot-pin" class="link-secondary">{$this->tr('auth.web.lock.forgot_pin')}</a>
        <span class="mx-2">•</span>
        <a href="/forgot-password" class="link-secondary">{$this->tr('auth.web.lock.forgot_password')}</a>
        <span class="mx-2">•</span>
        <a href="/exit" class="link-secondary">{$this->tr('auth.web.lock.switch_account')}</a>
      </div>
    </div>
  </div>
  {$copyright}
</div>
HTML, false);

        return Response::make($html, 200, ['Content-Type' => 'text/html; charset=utf-8']);
    }

    public function unlock(Request $request): Response
    {
        $user = Auth::guard('session')->user();
        if ($user === null) {
            return Response::redirect('/login');
        }

        $hasPin = method_exists($user, 'hasLockPin') ? (bool) $user->hasLockPin() : false;
        if ($hasPin) {
            $pin = trim((string) $request->input('pin', ''));
            if ($pin === '') {
                app(Logger::class)->channel('security')->warning('auth.unlock.failed', [
                    'reason' => 'pin_required',
                    'user_id' => (int) ($user->id ?? 0),
                ]);
                return Response::redirect('/lock?error=' . $this->lineForUrl('auth.web.lock.error_pin_required'));
            }

            $ok = method_exists($user, 'verifyLockPin') ? (bool) $user->verifyLockPin($pin) : false;
            if (!$ok) {
                app(Logger::class)->channel('security')->warning('auth.unlock.failed', [
                    'reason' => 'invalid_pin',
                    'user_id' => (int) ($user->id ?? 0),
                ]);
                return Response::redirect('/lock?error=' . $this->lineForUrl('auth.web.lock.error_pin_invalid'));
            }
        } else {
            $password = (string) $request->input('password', '');
            if ($password === '') {
                app(Logger::class)->channel('security')->warning('auth.unlock.failed', [
                    'reason' => 'password_required',
                    'user_id' => (int) ($user->id ?? 0),
                ]);
                return Response::redirect('/lock?error=' . $this->lineForUrl('auth.web.lock.error_required'));
            }

            $ok = method_exists($user, 'verifyPassword') ? (bool) $user->verifyPassword($password) : false;
            if (!$ok) {
                app(Logger::class)->channel('security')->warning('auth.unlock.failed', [
                    'reason' => 'invalid_password',
                    'user_id' => (int) ($user->id ?? 0),
                ]);
                return Response::redirect('/lock?error=' . $this->lineForUrl('auth.web.lock.error_invalid'));
            }
        }

        $redirect = $this->getLockReturnPath();
        $this->clearLockState();
        app(Logger::class)->channel('auth')->info('auth.unlock.success', [
            'user_id' => (int) ($user->id ?? 0),
            'redirect' => $redirect,
        ]);

        return Response::redirect($redirect);
    }

    private function loginBody(string $error, string $success, string $csrfToken): string
    {
        $errorHtml = $error !== ''
            ? '<div class="alert alert-danger" role="alert">' . htmlspecialchars($error, ENT_QUOTES, 'UTF-8') . '</div>'
            : '';
        $successHtml = $success !== ''
            ? '<div class="alert alert-success" role="alert">' . htmlspecialchars($success, ENT_QUOTES, 'UTF-8') . '</div>'
            : '';
        $coverUrl = htmlspecialchars($this->loginCoverUrl(), ENT_QUOTES, 'UTF-8');
        $appName = $this->appName();
        $brandLogo = $this->brandLogoHtml('kirpi-login-brand mx-auto mb-2');
        $copyright = $this->copyrightHtml('text-center text-secondary small mt-auto mb-4 px-3');

        return <<<HTML
<style>
  .kirpi-login-cover {
    min-height: 100vh;
  }
  .kirpi-login-brand {
    width: 56px;
    height: 56px;
    border-radius: 10px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #ffffff;
    border: 1px solid rgba(15, 23, 42, 0.08);
    padding: 8px;
  }
  .kirpi-login-brand img {
    width: 100%;
    height: 100%;
    object-fit: contain;
  }
  .kirpi-brand-logo {
    width: 56px;
    height: 56px;
    border-radius: 10px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #ffffff;
    border: 1px solid rgba(15, 23, 42, 0.08);
    padding: 8px;
  }
  .kirpi-brand-logo img {
    width: 100%;
    height: 100%;
    object-fit: contain;
  }
  .kirpi-login-photo {
    position: relative;
    min-height: 100vh;
    background: #fff url('{$coverUrl}') no-repeat;
    background-size: cover;
    background-position: left;
  }
  .kirpi-login-photo::before {
    content: "";
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 1px;
    background: rgba(15, 23, 42, 0.14);
  }
</style>
<div class="row g-0 flex-fill kirpi-login-cover">
  <div class="col-12 col-lg-6 col-xl-4 border-top border-4 border-primary d-flex flex-column bg-white">
    <div class="d-flex flex-column justify-content-center flex-grow-1">
    <div class="container container-tight my-5 px-lg-5">
      <div class="text-center mb-4">
        {$brandLogo}
        <div class="text-uppercase text-secondary small fw-semibold">{$appName}</div>
      </div>

      <h2 class="h3 text-center mb-3">{$this->tr('auth.web.login.title')}</h2>
      {$successHtml}
      {$errorHtml}
      <form method="post" action="/login" autocomplete="on">
        <input type="hidden" name="_token" value="{$csrfToken}">
        <div class="mb-3">
          <label class="form-label">{$this->tr('auth.web.fields.email')}</label>
          <input class="form-control" type="email" name="email" required>
        </div>
        <div class="mb-2">
          <label class="form-label">{$this->tr('auth.web.fields.password')}</label>
          <input class="form-control" type="password" name="password" required>
        </div>
        <div class="mb-3 text-end">
          <a href="/forgot-password" class="link-secondary">{$this->tr('auth.web.login.forgot')}</a>
        </div>
        <label class="form-check mb-3">
          <input class="form-check-input" type="checkbox" name="remember" value="1">
          <span class="form-check-label">{$this->tr('auth.web.login.remember')}</span>
        </label>
        <button class="btn btn-primary w-100" type="submit">{$this->tr('auth.web.login.submit')}</button>
      </form>
    </div>
    </div>
    {$copyright}
  </div>
  <div class="col-12 col-lg-6 col-xl-8 d-none d-lg-block kirpi-login-photo border-top border-4 border-primary"></div>
</div>
HTML;
    }

    private function loginCoverUrl(): string
    {
        $configured = trim((string) env('KIRPI_AUTH_LOGIN_COVER', ''));
        if ($configured !== '') {
            return $configured;
        }

        return 'https://s3.kirpinetwork.com/web/kirpi-framework/cover_kirpi.png';
    }

    private function appName(): string
    {
        return htmlspecialchars((string) config('app.name', 'Kirpi Framework'), ENT_QUOTES, 'UTF-8');
    }

    private function appLogoUrl(): string
    {
        return htmlspecialchars((string) config('app.logo', 'https://s3.kirpinetwork.com/web/kirpi.svg'), ENT_QUOTES, 'UTF-8');
    }

    private function brandLogoHtml(string $class = 'kirpi-login-brand'): string
    {
        $logoUrl = $this->appLogoUrl();
        return '<div class="' . $class . '"><img src="' . $logoUrl . '" alt="App Logo" loading="lazy"></div>';
    }

    private function copyrightHtml(string $class = 'text-center text-secondary small'): string
    {
        $year = date('Y');
        return '<div class="' . $class . '"><a href="https://kirpinetwork.com" target="_blank" rel="noreferrer" class="link-secondary">Copyright &copy; ' . $year . ' Kirpi Framework</a></div>';
    }

    private function tr(string $key, array $replace = []): string
    {
        return htmlspecialchars($this->line($key, $replace), ENT_QUOTES, 'UTF-8');
    }

    private function line(string $key, array $replace = []): string
    {
        return (string) __($key, $replace);
    }

    private function lineForUrl(string $key, array $replace = []): string
    {
        return rawurlencode($this->line($key, $replace));
    }


    private function renderTemplate(string $title, string $content, bool $container = true): string
    {
        $bodyClass = $container ? 'bg-body-tertiary' : 'bg-body';
        $wrapperStart = $container ? '<div class="container-xl py-5">' : '';
        $wrapperEnd = $container ? '</div>' : '';

        return <<<HTML
<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{$title}</title>
  <link rel="preconnect" href="https://s3.kirpinetwork.com" crossorigin>
  <link rel="stylesheet" href="/vendor/tabler/dist/css/tabler.min.css">
  <link rel="stylesheet" href="/vendor/tabler/dist/css/tabler-themes.css">
  <style>
    .kirpi-brand-logo,
    .kirpi-login-brand {
      width: 56px;
      height: 56px;
      border-radius: 10px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      background: #ffffff;
      border: 1px solid rgba(15, 23, 42, 0.08);
      padding: 8px;
    }
    .kirpi-brand-logo img,
    .kirpi-login-brand img {
      width: 100%;
      height: 100%;
      object-fit: contain;
    }
  </style>
</head>
<body class="{$bodyClass} layout-navbar-condensed">
  <script src="/vendor/tabler/dist/js/tabler-theme.min.js"></script>
  <div class="page">
    <div class="page-wrapper">
      {$wrapperStart}
        {$content}
      {$wrapperEnd}
    </div>
  </div>
</body>
</html>
HTML;
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

    private function lockCurrentSession(string $requestedReturn): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION['screen_locked'] = true;
        $_SESSION['lock_return'] = $this->sanitizeReturnPath($requestedReturn);
    }

    private function isSessionLocked(): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        return (bool) ($_SESSION['screen_locked'] ?? false);
    }

    private function clearLockState(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        unset($_SESSION['screen_locked'], $_SESSION['lock_return']);
    }

    private function getLockReturnPath(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $value = (string) ($_SESSION['lock_return'] ?? '/dashboard');
        return $this->sanitizeReturnPath($value);
    }

    private function markPinResetVerified(int $userId): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION['pin_reset_verified_user_id'] = $userId;
        $_SESSION['pin_reset_verified_at'] = time();
    }

    private function clearPinResetVerified(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        unset($_SESSION['pin_reset_verified_user_id'], $_SESSION['pin_reset_verified_at']);
    }

    private function isPinResetVerified(): bool
    {
        return $this->pinResetVerifiedUserId() !== null;
    }

    private function pinResetVerifiedUserId(): ?int
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $verifiedAt = (int) ($_SESSION['pin_reset_verified_at'] ?? 0);
        if ($verifiedAt <= 0 || (time() - $verifiedAt) > 600) {
            $this->clearPinResetVerified();
            return null;
        }

        $userId = (int) ($_SESSION['pin_reset_verified_user_id'] ?? 0);
        return $userId > 0 ? $userId : null;
    }

    private function sanitizeReturnPath(string $path): string
    {
        $path = trim($path);
        if ($path === '') {
            return '/dashboard';
        }

        $parsed = parse_url($path, PHP_URL_PATH);
        $query = parse_url($path, PHP_URL_QUERY);
        $cleanPath = is_string($parsed) ? '/' . ltrim($parsed, '/') : '/dashboard';
        if (!str_starts_with($cleanPath, '/')) {
            $cleanPath = '/dashboard';
        }

        if (in_array($cleanPath, ['/lock', '/lock-screen', '/login', '/exit'], true)) {
            return '/dashboard';
        }

        if (is_string($query) && $query !== '') {
            return $cleanPath . '?' . $query;
        }

        return $cleanPath;
    }

    private function issuePasswordResetToken(int $userId): string
    {
        $rawToken = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $rawToken);
        $expiresAt = date('Y-m-d H:i:s', time() + (30 * 60));

        $db = app(DatabaseManager::class);
        $db->table('password_reset_tokens')
            ->where('user_id', $userId)
            ->whereNull('used_at')
            ->delete();

        $db->table('password_reset_tokens')->insert([
            'user_id' => $userId,
            'token_hash' => $tokenHash,
            'expires_at' => $expiresAt,
            'used_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $rawToken;
    }

    private function findValidPasswordResetToken(string $rawToken): ?object
    {
        $tokenHash = hash('sha256', $rawToken);
        $db = app(DatabaseManager::class);
        $row = $db->table('password_reset_tokens')
            ->where('token_hash', $tokenHash)
            ->whereNull('used_at')
            ->first();

        if ($row === null) {
            return null;
        }

        $expiresAt = (string) ($row->expires_at ?? '');
        if ($expiresAt === '' || strtotime($expiresAt) < time()) {
            return null;
        }

        return $row;
    }

    private function markPasswordResetUsed(int $id): void
    {
        app(DatabaseManager::class)->table('password_reset_tokens')
            ->where('id', $id)
            ->update([
                'used_at' => now(),
                'updated_at' => now(),
            ]);
    }

    private function passwordResetUrl(string $token): string
    {
        $baseUrl = rtrim((string) config('app.url', 'http://localhost'), '/');
        return $baseUrl . '/forgot-password?token=' . rawurlencode($token);
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}

