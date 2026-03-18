<?php

declare(strict_types=1);

namespace Core\Exception;

use Core\Http\Request;
use Core\Http\Response;
use Core\Logging\Logger;

class Handler
{
    // Bu exception'lar loglanmaz
    private array $dontReport = [
        HttpException::class,
        ValidationException::class,
    ];

    public function __construct(
        private readonly Logger $logger,
        private readonly bool   $debug = false,
    ) {}

    // ─── Ana Handle ──────────────────────────────────────────

    public function handle(\Throwable $e, Request $request): Response
    {
        // Logla
        if ($this->shouldReport($e)) {
            $this->report($e);
        }

        // Response üret
        return $this->render($e, $request);
    }

    // ─── Render ──────────────────────────────────────────────

    private function render(\Throwable $e, Request $request): Response
    {
        // Validation hatası
        if ($e instanceof ValidationException) {
            return Response::unprocessable($e->errors());
        }

        // HTTP exception
        if ($e instanceof HttpException) {
            return $this->renderHttpException($e, $request);
        }

        // Beklenmedik hata
        return $this->renderServerError($e, $request);
    }

    private function renderHttpException(HttpException $e, Request $request): Response
    {
        $status  = $e->getStatusCode();
        $message = $e->getMessage() ?: $this->defaultMessage($status);

        if ($request->expectsJson()) {
            return Response::json([
                'error'   => $message,
                'status'  => $status,
            ], $status);
        }

        // HTML response — view varsa kullan
        $viewPath = base_path("resources/views/errors/{$status}.php");

        if (file_exists($viewPath)) {
            ob_start();
            include $viewPath;
            return Response::make(ob_get_clean(), $status);
        }

        return Response::make($this->defaultHtml($status, $message), $status);
    }

    private function renderServerError(\Throwable $e, Request $request): Response
    {
        if ($request->expectsJson()) {
            $data = ['error' => 'Server Error', 'status' => 500];

            if ($this->debug) {
                $data['exception'] = get_class($e);
                $data['message']   = $e->getMessage();
                $data['file']      = $e->getFile();
                $data['line']      = $e->getLine();
                $data['trace']     = explode("\n", $e->getTraceAsString());
            }

            return Response::json($data, 500);
        }

        if ($this->debug) {
            return Response::make(
                $this->debugHtml($e),
                500
            );
        }

        return Response::make(
            $this->defaultHtml(500, 'Server Error'),
            500
        );
    }

    // ─── Report ──────────────────────────────────────────────

    public function report(\Throwable $e): void
    {
        try {
            $this->logger->error($e->getMessage(), [
                'exception' => get_class($e),
                'file'      => $e->getFile(),
                'line'      => $e->getLine(),
                'trace'     => $e->getTraceAsString(),
            ]);
        } catch (\Throwable) {
            // Logger da hata verirse sessizce geç
        }
    }

    private function shouldReport(\Throwable $e): bool
    {
        foreach ($this->dontReport as $class) {
            if ($e instanceof $class) {
                return false;
            }
        }

        return true;
    }

    // ─── Global Register ─────────────────────────────────────

    public function register(): void
    {
        // PHP hata işleyicisi
        set_error_handler(function (int $level, string $message, string $file, int $line): bool {
            if (error_reporting() & $level) {
                throw new \ErrorException($message, 0, $level, $file, $line);
            }
            return false;
        });

        // Exception işleyicisi
        set_exception_handler(function (\Throwable $e): void {
            $request  = Request::capture();
            $response = $this->handle($e, $request);
            $response->send();
        });

        // Fatal error işleyicisi
        register_shutdown_function(function (): void {
            $error = error_get_last();

            if ($error !== null && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR])) {
                $e        = new \ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']);
                $request  = Request::capture();
                $response = $this->handle($e, $request);
                $response->send();
            }
        });
    }

    // ─── HTML Templates ──────────────────────────────────────

    private function defaultHtml(int $status, string $message): string
    {
        return <<<HTML
        <!DOCTYPE html>
        <html lang="tr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>{$status} — {$message}</title>
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body { font-family: Arial, sans-serif; background: #f5f5f5; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
                .box { background: white; padding: 48px; border-radius: 8px; text-align: center; box-shadow: 0 2px 16px rgba(0,0,0,.1); }
                h1 { font-size: 72px; color: #1B4F72; margin-bottom: 16px; }
                p  { color: #5D6D7E; font-size: 18px; }
                a  { color: #2E86C1; text-decoration: none; display: inline-block; margin-top: 24px; }
            </style>
        </head>
        <body>
            <div class="box">
                <h1>{$status}</h1>
                <p>{$message}</p>
                <a href="/">← Ana Sayfa</a>
            </div>
        </body>
        </html>
        HTML;
    }

    private function debugHtml(\Throwable $e): string
    {
        $class   = get_class($e);
        $message = htmlspecialchars($e->getMessage());
        $file    = htmlspecialchars($e->getFile());
        $line    = $e->getLine();
        $trace   = htmlspecialchars($e->getTraceAsString());

        return <<<HTML
        <!DOCTYPE html>
        <html lang="tr">
        <head>
            <meta charset="UTF-8">
            <title>500 — {$class}</title>
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body { font-family: Arial, sans-serif; background: #1C2833; color: #ECF0F1; padding: 32px; }
                .header { background: #E74C3C; padding: 24px; border-radius: 8px; margin-bottom: 24px; }
                .header h1 { font-size: 20px; margin-bottom: 8px; }
                .header p  { opacity: .8; font-size: 14px; }
                .card { background: #2C3E50; padding: 24px; border-radius: 8px; margin-bottom: 16px; }
                .card h2 { font-size: 14px; text-transform: uppercase; opacity: .6; margin-bottom: 12px; }
                pre { font-family: monospace; font-size: 13px; line-height: 1.6; overflow-x: auto; white-space: pre-wrap; }
                .file { color: #3498DB; }
                .line { color: #E74C3C; font-weight: bold; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>🦔 Kirpi — {$class}</h1>
                <p>{$message}</p>
            </div>
            <div class="card">
                <h2>Konum</h2>
                <pre><span class="file">{$file}</span> : <span class="line">{$line}</span></pre>
            </div>
            <div class="card">
                <h2>Stack Trace</h2>
                <pre>{$trace}</pre>
            </div>
        </body>
        </html>
        HTML;
    }

    private function defaultMessage(int $status): string
    {
        return match($status) {
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            419 => 'Page Expired',
            422 => 'Unprocessable Entity',
            429 => 'Too Many Requests',
            500 => 'Server Error',
            503 => 'Service Unavailable',
            default => 'Error',
        };
    }
}