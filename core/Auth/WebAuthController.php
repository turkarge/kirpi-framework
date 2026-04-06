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
      <form action="/logout" method="post">
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

    private function loginBody(string $error, string $csrfToken): string
    {
        $errorHtml = $error !== ''
            ? '<div class="alert alert-danger" role="alert">' . htmlspecialchars($error, ENT_QUOTES, 'UTF-8') . '</div>'
            : '';

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
    background:
      linear-gradient(135deg, rgba(30, 41, 59, .85), rgba(15, 23, 42, .75)),
      radial-gradient(circle at 25% 20%, rgba(59, 130, 246, .35), transparent 45%),
      radial-gradient(circle at 75% 80%, rgba(16, 185, 129, .28), transparent 40%);
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
          <a href="#" class="link-secondary">Sifremi unuttum</a>
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
