<?php

declare(strict_types=1);

namespace Core\Auth;

use Core\Auth\Facades\Auth;
use Core\Http\Request;
use Core\Http\Response;

class WebAuthController
{
    public function showLogin(Request $request): Response
    {
        $csrfToken = $this->csrfToken();

        $error = trim((string) $request->get('error', ''));
        $html = $this->renderTemplate($this->line('auth.web.login.meta_title'), $this->loginBody($error, $csrfToken), false);

        return Response::make($html, 200, ['Content-Type' => 'text/html; charset=utf-8']);
    }

    public function login(Request $request): Response
    {
        $email = trim((string) $request->input('email', ''));
        $password = (string) $request->input('password', '');
        $remember = (bool) $request->boolean('remember', false);

        if ($email === '' || $password === '') {
            return Response::redirect('/login?error=' . $this->lineForUrl('auth.web.login.error_required'));
        }

        $ok = Auth::guard('session')->attempt([
            'email' => $email,
            'password' => $password,
        ], $remember);

        if (!$ok) {
            return Response::redirect('/login?error=' . $this->lineForUrl('auth.web.login.error_invalid'));
        }

        return Response::redirect('/dashboard');
    }

    public function showForgotPassword(Request $request): Response
    {
        $error = trim((string) $request->get('error', ''));
        $success = trim((string) $request->get('success', ''));
        $csrfToken = $this->csrfToken();

        $errorHtml = $error !== ''
            ? '<div class="alert alert-danger" role="alert">' . htmlspecialchars($error, ENT_QUOTES, 'UTF-8') . '</div>'
            : '';
        $successHtml = $success !== ''
            ? '<div class="alert alert-success" role="alert">' . htmlspecialchars($success, ENT_QUOTES, 'UTF-8') . '</div>'
            : '';
        $appName = $this->appName();
        $brandLogo = $this->brandLogoHtml('kirpi-brand-logo mx-auto mb-2');
        $copyright = $this->copyrightHtml('text-center text-secondary small mt-auto mb-4 px-3');

        $html = $this->renderTemplate($this->line('auth.web.forgot.meta_title'), <<<HTML
<div class="container container-tight py-5 min-vh-100 d-flex flex-column">
  <div class="my-auto">
    <div class="text-center mb-4">
      {$brandLogo}
      <div class="text-uppercase text-secondary small fw-semibold mt-2">{$appName}</div>
      <h1 class="h3 mt-2 mb-0">{$this->tr('auth.web.forgot.title')}</h1>
    </div>
    <div class="card card-md">
      <div class="card-body">
        <p class="text-secondary">
          {$this->tr('auth.web.forgot.description')}
        </p>
        {$errorHtml}
        {$successHtml}
        <form method="post" action="/forgot-password" autocomplete="on">
          <input type="hidden" name="_token" value="{$csrfToken}">
          <div class="mb-3">
            <label class="form-label">{$this->tr('auth.web.fields.email')}</label>
            <input class="form-control" type="email" name="email" required>
          </div>
          <button class="btn btn-primary w-100" type="submit">{$this->tr('auth.web.forgot.submit')}</button>
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

        return Response::redirect('/forgot-password?success=' . $this->lineForUrl('auth.web.forgot.success'));
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

    public function dashboard(): Response
    {
        $user = Auth::guard('session')->user();
        $name = htmlspecialchars((string) ($user?->name ?? 'User'), ENT_QUOTES, 'UTF-8');
        $email = htmlspecialchars((string) ($user?->email ?? '-'), ENT_QUOTES, 'UTF-8');
        $csrfToken = $this->csrfToken();
        $appName = $this->appName();

        $html = $this->renderTemplate($this->line('auth.web.dashboard.meta_title'), <<<HTML
<div class="page-header d-print-none mb-4">
  <div class="row align-items-center">
    <div class="col">
      <h2 class="page-title">{$this->tr('auth.web.dashboard.title')}</h2>
      <div class="text-secondary">{$this->tr('auth.web.dashboard.subtitle')}</div>
    </div>
    <div class="col-auto">
      <form action="/exit" method="post">
        <input type="hidden" name="_token" value="{$csrfToken}">
        <button class="btn btn-outline-secondary" type="submit">{$this->tr('auth.web.common.logout')}</button>
      </form>
    </div>
  </div>
</div>

<div class="row g-3">
  <div class="col-12 col-lg-8">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">{$this->tr('auth.web.dashboard.welcome', ['name' => html_entity_decode($name, ENT_QUOTES, 'UTF-8')])}</h3>
      </div>
      <div class="card-body">
        <p class="text-secondary mb-3">
          {$this->tr('auth.web.dashboard.description', ['app' => html_entity_decode($appName, ENT_QUOTES, 'UTF-8')])}
        </p>
        <div class="btn-list">
          <a class="btn btn-primary" href="/">{$this->tr('auth.web.dashboard.landing')}</a>
          <a class="btn btn-outline-primary" href="/health" target="_blank" rel="noreferrer">Health</a>
          <a class="btn btn-outline-primary" href="/ready" target="_blank" rel="noreferrer">Ready</a>
        </div>
      </div>
    </div>
  </div>
  <div class="col-12 col-lg-4">
    <div class="card">
      <div class="card-header"><h3 class="card-title">{$this->tr('auth.web.dashboard.account_summary')}</h3></div>
      <div class="card-body">
        <div class="mb-2"><span class="text-secondary">{$this->tr('auth.web.fields.user')}:</span> {$name}</div>
        <div class="mb-2"><span class="text-secondary">{$this->tr('auth.web.fields.email')}:</span> {$email}</div>
        <div><span class="text-secondary">{$this->tr('auth.web.fields.guard')}:</span> session</div>
      </div>
    </div>
  </div>
</div>
HTML);

        return Response::make($html, 200, ['Content-Type' => 'text/html; charset=utf-8']);
    }

    public function logout(): Response
    {
        Auth::guard('session')->logout();
        return Response::redirect('/login');
    }

    public function showLockScreen(Request $request): Response
    {
        $user = Auth::guard('session')->user();
        $name = htmlspecialchars((string) ($user?->name ?? 'User'), ENT_QUOTES, 'UTF-8');
        $email = htmlspecialchars((string) ($user?->email ?? ''), ENT_QUOTES, 'UTF-8');
        $hasUserEmail = $email !== '';
        $csrfToken = $this->csrfToken();
        $error = trim((string) $request->get('error', ''));
        $errorHtml = $error !== ''
            ? '<div class="alert alert-danger mb-3" role="alert">' . htmlspecialchars($error, ENT_QUOTES, 'UTF-8') . '</div>'
            : '';
        $appName = $this->appName();
        $brandLogo = $this->brandLogoHtml('kirpi-brand-logo mx-auto mb-2');
        $copyright = $this->copyrightHtml('text-center text-secondary small mt-auto mb-4 px-3');
        $emailField = $hasUserEmail
            ? '<input type="hidden" name="email" value="' . $email . '">'
            : <<<HTML
        <div class="mb-3">
          <label class="form-label">E-posta</label>
          <input class="form-control" type="email" name="email" required>
        </div>
HTML;

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
        {$errorHtml}
        <form method="post" action="/lock">
          <input type="hidden" name="_token" value="{$csrfToken}">
          {$emailField}
          <div class="mb-3">
            <label class="form-label">{$this->tr('auth.web.fields.password')}</label>
            <input class="form-control" type="password" name="password" required>
          </div>
          <button class="btn btn-primary w-100" type="submit">{$this->tr('auth.web.lock.submit')}</button>
        </form>
      </div>
      <div class="card-footer text-center">
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
        $email = trim((string) ($user?->email ?? $request->input('email', '')));
        $password = (string) $request->input('password', '');

        if ($email === '' || $password === '') {
            return Response::redirect('/lock?error=' . $this->lineForUrl('auth.web.lock.error_required'));
        }

        $ok = Auth::guard('session')->attempt([
            'email' => $email,
            'password' => $password,
        ], true);

        if (!$ok) {
            return Response::redirect('/lock?error=' . $this->lineForUrl('auth.web.lock.error_invalid'));
        }

        return Response::redirect('/dashboard');
    }

    private function loginBody(string $error, string $csrfToken): string
    {
        $errorHtml = $error !== ''
            ? '<div class="alert alert-danger" role="alert">' . htmlspecialchars($error, ENT_QUOTES, 'UTF-8') . '</div>'
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
  <link rel="stylesheet" href="/vendor/tabler/dist/css/tabler.min.css">
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
<body class="{$bodyClass}">
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
}
