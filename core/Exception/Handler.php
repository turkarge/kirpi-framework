<?php

declare(strict_types=1);

namespace Core\Exception;

use Core\Http\Request;
use Core\Http\Response;
use Core\Logging\Logger;

class Handler
{
    /** @var list<class-string<\Throwable>> */
    private array $dontReport = [
        HttpException::class,
        ValidationException::class,
    ];

    public function __construct(
        private readonly Logger $logger,
        private readonly bool $debug = false,
    ) {}

    public function handle(\Throwable $e, Request $request): Response
    {
        $requestId = $this->resolveRequestId($request);

        if ($this->shouldReport($e)) {
            $this->report($e, $request, $requestId);
        }

        $response = $this->render($e, $request, $requestId);

        return $response->header('X-Request-Id', $requestId);
    }

    private function render(\Throwable $e, Request $request, string $requestId): Response
    {
        if ($e instanceof ValidationException) {
            return Response::json([
                'errors' => $e->errors(),
                'request_id' => $requestId,
            ], 422);
        }

        if ($e instanceof HttpException) {
            return $this->renderHttpException($e, $request, $requestId);
        }

        return $this->renderServerError($e, $request, $requestId);
    }

    private function renderHttpException(HttpException $e, Request $request, string $requestId): Response
    {
        $status = $e->getStatusCode();
        $message = $e->getMessage() !== '' ? $e->getMessage() : $this->defaultMessage($status);

        if ($request->expectsJson()) {
            return Response::json([
                'error' => $message,
                'status' => $status,
                'request_id' => $requestId,
            ], $status);
        }

        $viewPath = base_path("resources/views/errors/{$status}.php");
        if (is_file($viewPath)) {
            ob_start();
            $requestIdForView = $requestId;
            $statusForView = $status;
            $messageForView = $message;
            include $viewPath;
            return Response::make((string) ob_get_clean(), $status, ['Content-Type' => 'text/html; charset=utf-8']);
        }

        return Response::make(
            $this->defaultHtml($status, $message, $requestId),
            $status,
            ['Content-Type' => 'text/html; charset=utf-8']
        );
    }

    private function renderServerError(\Throwable $e, Request $request, string $requestId): Response
    {
        if ($request->expectsJson()) {
            $payload = [
                'error' => 'Server Error',
                'status' => 500,
                'request_id' => $requestId,
            ];

            if ($this->debug) {
                $payload['exception'] = get_class($e);
                $payload['message'] = $e->getMessage();
                $payload['file'] = $e->getFile();
                $payload['line'] = $e->getLine();
            }

            return Response::json($payload, 500);
        }

        if ($this->debug) {
            return Response::make(
                $this->debugHtml($e, $requestId),
                500,
                ['Content-Type' => 'text/html; charset=utf-8']
            );
        }

        $viewPath = base_path('resources/views/errors/500.php');
        if (is_file($viewPath)) {
            ob_start();
            $requestIdForView = $requestId;
            $statusForView = 500;
            $messageForView = 'Server Error';
            include $viewPath;
            return Response::make((string) ob_get_clean(), 500, ['Content-Type' => 'text/html; charset=utf-8']);
        }

        return Response::make(
            $this->defaultHtml(500, 'Server Error', $requestId),
            500,
            ['Content-Type' => 'text/html; charset=utf-8']
        );
    }

    private function report(\Throwable $e, Request $request, string $requestId): void
    {
        try {
            $this->logger->error($e->getMessage(), [
                'request_id' => $requestId,
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'method' => $request->method(),
                'path' => $request->path(),
                'ip' => $request->ip(),
            ]);
        } catch (\Throwable) {
            // Keep exception handling safe even if logging fails.
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

    public function register(): void
    {
        set_error_handler(function (int $level, string $message, string $file, int $line): bool {
            if (error_reporting() & $level) {
                throw new \ErrorException($message, 0, $level, $file, $line);
            }
            return false;
        });

        set_exception_handler(function (\Throwable $e): void {
            $request = Request::capture();
            $this->handle($e, $request)->send();
        });

        register_shutdown_function(function (): void {
            $error = error_get_last();
            if ($error === null) {
                return;
            }

            if (!in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
                return;
            }

            $exception = new \ErrorException(
                $error['message'],
                0,
                $error['type'],
                $error['file'],
                $error['line']
            );

            $request = Request::capture();
            $this->handle($exception, $request)->send();
        });
    }

    private function resolveRequestId(Request $request): string
    {
        $headerId = trim((string) $request->header('X-Request-Id', ''));
        if ($headerId !== '') {
            return $headerId;
        }

        return bin2hex(random_bytes(8));
    }

    private function defaultHtml(int $status, string $message, string $requestId): string
    {
        $safeMessage = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
        $safeRequestId = htmlspecialchars($requestId, ENT_QUOTES, 'UTF-8');

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{$status} - {$safeMessage}</title>
  <style>
    body { font-family: Arial, sans-serif; background: #f5f7fb; color: #182433; margin: 0; }
    .box { max-width: 560px; margin: 10vh auto; background: #fff; border: 1px solid #dce1e7; border-radius: 8px; padding: 24px; }
    h1 { margin: 0 0 8px; font-size: 42px; }
    p { margin: 0 0 10px; color: #667382; }
    .rid { font-family: monospace; background: #f6f8fb; border: 1px solid #dce1e7; padding: 8px 10px; border-radius: 6px; display: inline-block; }
  </style>
</head>
<body>
  <div class="box">
    <h1>{$status}</h1>
    <p>{$safeMessage}</p>
    <p>Request ID: <span class="rid">{$safeRequestId}</span></p>
    <p><a href="/">Back to home</a></p>
  </div>
</body>
</html>
HTML;
    }

    private function debugHtml(\Throwable $e, string $requestId): string
    {
        $class = htmlspecialchars(get_class($e), ENT_QUOTES, 'UTF-8');
        $message = htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        $file = htmlspecialchars($e->getFile(), ENT_QUOTES, 'UTF-8');
        $line = $e->getLine();
        $safeRequestId = htmlspecialchars($requestId, ENT_QUOTES, 'UTF-8');
        $trace = htmlspecialchars($e->getTraceAsString(), ENT_QUOTES, 'UTF-8');

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>500 - {$class}</title>
  <style>
    body { font-family: Arial, sans-serif; background: #111827; color: #e5e7eb; margin: 0; padding: 20px; }
    .card { background: #1f2937; border: 1px solid #374151; border-radius: 8px; padding: 16px; margin-bottom: 14px; }
    h1, h2 { margin: 0 0 10px; }
    pre { white-space: pre-wrap; overflow-wrap: anywhere; }
    .rid { color: #93c5fd; font-family: monospace; }
  </style>
</head>
<body>
  <div class="card">
    <h1>{$class}</h1>
    <p>{$message}</p>
    <p>Request ID: <span class="rid">{$safeRequestId}</span></p>
  </div>
  <div class="card">
    <h2>Location</h2>
    <p>{$file}:{$line}</p>
  </div>
  <div class="card">
    <h2>Trace</h2>
    <pre>{$trace}</pre>
  </div>
</body>
</html>
HTML;
    }

    private function defaultMessage(int $status): string
    {
        return match ($status) {
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

