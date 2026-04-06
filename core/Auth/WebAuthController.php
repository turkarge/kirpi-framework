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
        $html = $this->renderTemplate('Kirpi Login', $this->loginBody($error, $csrfToken), false);

        return Response::make($html, 200, ['Content-Type' => 'text/html; charset=utf-8']);
    }

    public function login(Request $request): Response
    {
        $email = trim((string) $request->input('email', ''));
        $password = (string) $request->input('password', '');
        $remember = (bool) $request->boolean('remember', false);

        if ($email === '' || $password === '') {
            return Response::redirect('/login?error=Email%20ve%20sifre%20zorunludur.');
        }

        $ok = Auth::guard('session')->attempt([
            'email' => $email,
            'password' => $password,
        ], $remember);

        if (!$ok) {
            return Response::redirect('/login?error=Giris%20bilgileri%20gecersiz.');
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

        $html = $this->renderTemplate('Sifre Hatirlat', <<<HTML
<div class="container container-tight py-5">
  <div class="text-center mb-4">
    <div class="text-uppercase text-secondary small fw-semibold">Kirpi Framework</div>
    <h1 class="h3 mt-2 mb-0">Sifreni mi unuttun?</h1>
  </div>
  <div class="card card-md">
    <div class="card-body">
      <p class="text-secondary">
        E-posta adresini gir. Sifre sifirlama baglantisini e-posta ile gonderelim.
      </p>
      {$errorHtml}
      {$successHtml}
      <form method="post" action="/forgot-password" autocomplete="on">
        <input type="hidden" name="_token" value="{$csrfToken}">
        <div class="mb-3">
          <label class="form-label">E-posta</label>
          <input class="form-control" type="email" name="email" required>
        </div>
        <button class="btn btn-primary w-100" type="submit">Sifirlama Linki Gonder</button>
      </form>
    </div>
    <div class="hr-text">veya</div>
    <div class="card-body">
      <a class="btn btn-outline-secondary w-100" href="/login">Login ekranina don</a>
    </div>
    <div class="card-footer text-center text-secondary">
      Devam ederek <a href="/tos" class="link-secondary">Kullanim Sartlarini</a> kabul etmis olursun.
    </div>
  </div>
</div>
HTML, false);

        return Response::make($html, 200, ['Content-Type' => 'text/html; charset=utf-8']);
    }

    public function forgotPassword(Request $request): Response
    {
        $email = trim((string) $request->input('email', ''));
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return Response::redirect('/forgot-password?error=Gecerli%20bir%20e-posta%20gir.');
        }

        return Response::redirect('/forgot-password?success=Sifre%20sifirlama%20baglantisi%20hazirlandi%20(simulasyon).');
    }

    public function termsOfService(): Response
    {
        $html = $this->renderTemplate('Kullanim Sartlari', <<<HTML
<div class="container container-narrow py-5">
  <div class="card card-md">
    <div class="card-body">
      <h3 class="card-title">Kullanim Sartlari</h3>
      <div class="text-secondary">
        <p>Kirpi Framework kisisel ve kucuk/orta olcekli uygulamalar icin tasarlanmis bir cekirdektir.</p>
        <p>Bu ornek sayfa bir yasal taslak yerine UI ve akisi dogrulamak icin tutulur.</p>
        <p>Uretim ortami icin uygulamana ozel gizlilik politikasi ve kullanim sartlarini eklemen gerekir.</p>
      </div>
      <div class="mt-4">
        <a class="btn btn-primary" href="/login">Login ekranina don</a>
      </div>
    </div>
  </div>
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
        $appName = htmlspecialchars((string) config('app.name', 'Kirpi Framework'), ENT_QUOTES, 'UTF-8');

        $html = $this->renderTemplate('Kirpi Core Dashboard', <<<HTML
<div class="page-header d-print-none mb-4">
  <div class="row align-items-center">
    <div class="col">
      <h2 class="page-title">Core Dashboard</h2>
      <div class="text-secondary">Kimlik dogrulama sonrasi varsayilan kontrol noktasi.</div>
    </div>
    <div class="col-auto">
      <form action="/exit" method="post">
        <input type="hidden" name="_token" value="{$csrfToken}">
        <button class="btn btn-outline-secondary" type="submit">Cikis</button>
      </form>
    </div>
  </div>
</div>

<div class="row g-3">
  <div class="col-12 col-lg-8">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Hos Geldin, {$name}</h3>
      </div>
      <div class="card-body">
        <p class="text-secondary mb-3">
          {$appName} cekirdegi hazir. Bundan sonraki adimda uygulamana ozel modulleri
          `make:module` ve `make:crud` komutlariyla ekleyebilirsin.
        </p>
        <div class="btn-list">
          <a class="btn btn-primary" href="/">Landing</a>
          <a class="btn btn-outline-primary" href="/health" target="_blank" rel="noreferrer">Health</a>
          <a class="btn btn-outline-primary" href="/ready" target="_blank" rel="noreferrer">Ready</a>
        </div>
      </div>
    </div>
  </div>
  <div class="col-12 col-lg-4">
    <div class="card">
      <div class="card-header"><h3 class="card-title">Hesap Ozeti</h3></div>
      <div class="card-body">
        <div class="mb-2"><span class="text-secondary">Kullanici:</span> {$name}</div>
        <div class="mb-2"><span class="text-secondary">E-posta:</span> {$email}</div>
        <div><span class="text-secondary">Guard:</span> session</div>
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
        $emailField = $hasUserEmail
            ? '<input type="hidden" name="email" value="' . $email . '">'
            : <<<HTML
        <div class="mb-3">
          <label class="form-label">E-posta</label>
          <input class="form-control" type="email" name="email" required>
        </div>
HTML;

        $html = $this->renderTemplate('Kilidi Ac', <<<HTML
<div class="container container-tight py-5">
  <div class="text-center mb-4">
    <div class="avatar avatar-xl mb-3 bg-dark text-white">KF</div>
    <h2 class="h3 mb-1">{$name}</h2>
    <div class="text-secondary">Oturum kilitlendi. Devam etmek icin sifreni gir.</div>
  </div>
  <div class="card card-md">
    <div class="card-body">
      {$errorHtml}
      <form method="post" action="/lock">
        <input type="hidden" name="_token" value="{$csrfToken}">
        {$emailField}
        <div class="mb-3">
          <label class="form-label">Sifre</label>
          <input class="form-control" type="password" name="password" required>
        </div>
        <button class="btn btn-primary w-100" type="submit">Kilidi Ac</button>
      </form>
    </div>
    <div class="card-footer text-center">
      <a href="/exit" class="link-secondary">Farkli hesapla giris yap</a>
    </div>
  </div>
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
            return Response::redirect('/lock?error=Sifre%20zorunludur.');
        }

        $ok = Auth::guard('session')->attempt([
            'email' => $email,
            'password' => $password,
        ], true);

        if (!$ok) {
            return Response::redirect('/lock?error=Hatali%20sifre.');
        }

        return Response::redirect('/dashboard');
    }

    private function loginBody(string $error, string $csrfToken): string
    {
        $errorHtml = $error !== ''
            ? '<div class="alert alert-danger" role="alert">' . htmlspecialchars($error, ENT_QUOTES, 'UTF-8') . '</div>'
            : '';
        $coverUrl = htmlspecialchars($this->loginCoverUrl(), ENT_QUOTES, 'UTF-8');

        return <<<HTML
<style>
  .kirpi-login-cover {
    min-height: 100vh;
  }
  .kirpi-login-brand {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #1f2937;
    color: #fff;
    font-weight: 700;
  }
  .kirpi-login-photo {
    min-height: 100vh;
    background: #fff url('{$coverUrl}') no-repeat;
    background-size: cover;
    background-position: left;
  }
</style>
<div class="row g-0 flex-fill kirpi-login-cover">
  <div class="col-12 col-lg-6 col-xl-4 border-top border-4 border-primary d-flex flex-column justify-content-center bg-white">
    <div class="container container-tight my-5 px-lg-5">
      <div class="text-center mb-4">
        <div class="kirpi-login-brand mx-auto mb-2">KF</div>
        <div class="text-uppercase text-secondary small fw-semibold">Kirpi Framework</div>
      </div>

      <h2 class="h3 text-center mb-3">Hesabina Giris Yap</h2>
      {$errorHtml}
      <form method="post" action="/login" autocomplete="on">
        <input type="hidden" name="_token" value="{$csrfToken}">
        <div class="mb-3">
          <label class="form-label">E-posta</label>
          <input class="form-control" type="email" name="email" required>
        </div>
        <div class="mb-2">
          <label class="form-label">Sifre</label>
          <input class="form-control" type="password" name="password" required>
        </div>
        <div class="mb-3 text-end">
          <a href="/forgot-password" class="link-secondary">Sifremi unuttum</a>
        </div>
        <label class="form-check mb-3">
          <input class="form-check-input" type="checkbox" name="remember" value="1">
          <span class="form-check-label">Beni hatirla</span>
        </label>
        <button class="btn btn-primary w-100" type="submit">Giris Yap</button>
      </form>
    </div>
  </div>
  <div class="col-12 col-lg-6 col-xl-8 d-none d-lg-block kirpi-login-photo"></div>
</div>
HTML;
    }

    private function loginCoverUrl(): string
    {
        $configured = trim((string) env('KIRPI_AUTH_LOGIN_COVER', ''));
        if ($configured !== '') {
            return $configured;
        }

        return 'https://s3.kirpinetwork.com/web/kirpi-framework/cover_kirpi_framework.png';
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
