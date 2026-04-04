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
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['_token']) || !is_string($_SESSION['_token'])) {
            $_SESSION['_token'] = bin2hex(random_bytes(32));
        }

        $error = trim((string) $request->get('error', ''));
        $html = $this->renderTemplate('Kirpi Login', $this->loginBody($error, $_SESSION['_token']));

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

        $html = $this->renderTemplate('Kirpi Core Dashboard', <<<HTML
<div class="card card-md">
  <div class="card-body text-center">
    <h2 class="mb-2">Kirpi Core Dashboard</h2>
    <p class="text-secondary mb-4">Hos geldin {$name}. Buradan uygulama modullerini gelistirmeye baslayabilirsin.</p>
    <div class="d-flex justify-content-center gap-2">
      <a class="btn btn-primary" href="/monitor">Sistem Monitor</a>
      <form action="/logout" method="post" class="d-inline">
        <button class="btn btn-outline-secondary" type="submit">Cikis</button>
      </form>
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
<div class="row justify-content-center">
  <div class="col-12 col-md-6 col-lg-4">
    <div class="card card-md">
      <div class="card-body">
        <h2 class="h3 mb-3">Kirpi Giris</h2>
        {$errorHtml}
        <form method="post" action="/login">
          <input type="hidden" name="_token" value="{$csrfToken}">
          <div class="mb-3">
            <label class="form-label">E-posta</label>
            <input class="form-control" type="email" name="email" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Sifre</label>
            <input class="form-control" type="password" name="password" required>
          </div>
          <label class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="remember" value="1">
            <span class="form-check-label">Beni hatirla</span>
          </label>
          <button class="btn btn-primary w-100" type="submit">Giris Yap</button>
        </form>
      </div>
    </div>
  </div>
</div>
HTML;
    }

    private function renderTemplate(string $title, string $content): string
    {
        return <<<HTML
<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{$title}</title>
  <link rel="stylesheet" href="/vendor/tabler/dist/css/tabler.min.css">
</head>
<body class="bg-body-tertiary">
  <div class="page">
    <div class="page-wrapper">
      <div class="container-xl py-5">
        {$content}
      </div>
    </div>
  </div>
</body>
</html>
HTML;
    }
}

