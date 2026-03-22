<?php

declare(strict_types=1);

namespace Manager\Http\Controllers;

use Core\Console\Commands\MakeCrudCommand;
use Core\Console\Commands\MakeModuleCommand;
use Core\Http\Request;
use Core\Http\Response;
use Core\Mail\Mailable;
use Core\Routing\Router;
use Core\Runtime\RuntimeDiagnostics;

class ControlPlaneController
{
    public function dashboard(Request $request): Response
    {
        $token = trim((string) $request->get('token', ''));
        $html = $this->render('dashboard', [
            'token' => $token,
            'appEnv' => (string) env('APP_ENV', 'local'),
            'appUrl' => (string) env('APP_URL', 'http://localhost'),
            'phpVersion' => PHP_VERSION,
            'currentPath' => '/manager',
        ]);

        return Response::make($html, 200, ['Content-Type' => 'text/html; charset=utf-8']);
    }

    public function modulesPage(Request $request): Response
    {
        $token = trim((string) $request->get('token', ''));
        $html = $this->render('modules', [
            'token' => $token,
            'appEnv' => (string) env('APP_ENV', 'local'),
            'appUrl' => (string) env('APP_URL', 'http://localhost'),
            'phpVersion' => PHP_VERSION,
            'currentPath' => '/manager/modules',
        ]);

        return Response::make($html, 200, ['Content-Type' => 'text/html; charset=utf-8']);
    }

    public function customModulesPage(Request $request): Response
    {
        $token = trim((string) $request->get('token', ''));
        $html = $this->render('custom-modules', [
            'token' => $token,
            'appEnv' => (string) env('APP_ENV', 'local'),
            'appUrl' => (string) env('APP_URL', 'http://localhost'),
            'phpVersion' => PHP_VERSION,
            'currentPath' => '/manager/custom-modules',
        ]);

        return Response::make($html, 200, ['Content-Type' => 'text/html; charset=utf-8']);
    }

    public function mailPage(Request $request): Response
    {
        $token = trim((string) $request->get('token', ''));
        $html = $this->render('mail', [
            'token' => $token,
            'appEnv' => (string) env('APP_ENV', 'local'),
            'appUrl' => (string) env('APP_URL', 'http://localhost'),
            'phpVersion' => PHP_VERSION,
            'currentPath' => '/manager/mail',
        ]);

        return Response::make($html, 200, ['Content-Type' => 'text/html; charset=utf-8']);
    }

    public function testsPage(Request $request): Response
    {
        $token = trim((string) $request->get('token', ''));
        $html = $this->render('tests', [
            'token' => $token,
            'appEnv' => (string) env('APP_ENV', 'local'),
            'appUrl' => (string) env('APP_URL', 'http://localhost'),
            'phpVersion' => PHP_VERSION,
            'currentPath' => '/manager/tests',
        ]);

        return Response::make($html, 200, ['Content-Type' => 'text/html; charset=utf-8']);
    }

    public function overview(): Response
    {
        /** @var Router $router */
        $router = app(Router::class);
        $routes = $router->getRoutes()->all();

        $payload = [
            'ok' => true,
            'data' => [
                'context' => (string) env('APP_CONTEXT', 'app'),
                'env' => (string) env('APP_ENV', 'local'),
                'debug' => (bool) env('APP_DEBUG', false),
                'routes_total' => count($routes),
                'modules_total' => count($this->discoverModules()),
                'features' => [
                    'monitoring' => (bool) env('KIRPI_FEATURE_MONITORING', true),
                    'communication' => (bool) env('KIRPI_FEATURE_COMMUNICATION', true),
                    'ai' => (bool) env('KIRPI_FEATURE_AI', false),
                ],
            ],
        ];

        return Response::json($payload);
    }

    public function modules(): Response
    {
        return Response::json([
            'ok' => true,
            'data' => $this->discoverModules(),
        ]);
    }

    public function env(): Response
    {
        $rows = $this->readEnvMasked();

        return Response::json([
            'ok' => true,
            'data' => [
                'count' => count($rows),
                'rows' => $rows,
            ],
        ]);
    }

    public function generateModule(Request $request): Response
    {
        $name = trim((string) $request->get('name', ''));
        if ($name === '') {
            return Response::json(['ok' => false, 'error' => 'Module name is required.'], 422);
        }

        $result = $this->runCommand(MakeModuleCommand::class, ['make:module', $name]);

        return Response::json([
            'ok' => $result['exit_code'] === 0,
            'data' => $result,
        ], $result['exit_code'] === 0 ? 200 : 422);
    }

    public function generateCrud(Request $request): Response
    {
        $module = trim((string) $request->get('module', ''));
        $resource = trim((string) $request->get('resource', ''));

        if ($module === '' || $resource === '') {
            return Response::json(['ok' => false, 'error' => 'Module and resource are required.'], 422);
        }

        $result = $this->runCommand(MakeCrudCommand::class, ['make:crud', $module, $resource]);

        return Response::json([
            'ok' => $result['exit_code'] === 0,
            'data' => $result,
        ], $result['exit_code'] === 0 ? 200 : 422);
    }

    public function mailTest(Request $request): Response
    {
        $to = trim((string) $request->get('to', ''));
        if ($to === '') {
            return Response::json(['ok' => false, 'error' => 'Recipient email is required.'], 422);
        }

        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return Response::json(['ok' => false, 'error' => 'Recipient email is invalid.'], 422);
        }

        if (!app()->bound(\Core\Mail\MailManager::class)) {
            return Response::json([
                'ok' => false,
                'error' => 'Mail feature is disabled. Enable KIRPI_FEATURE_COMMUNICATION.',
            ], 503);
        }

        $mail = new class($to) extends Mailable {
            public function __construct(private readonly string $toAddress) {}
            public function build(): static
            {
                return $this
                    ->to($this->toAddress)
                    ->subject('Kirpi Manager Mail Test')
                    ->text('Kirpi Manager panelinden test maili gonderildi. Saat: ' . date('Y-m-d H:i:s'));
            }
        };

        $sent = mail_manager()->send($mail->build());

        return Response::json([
            'ok' => (bool) $sent,
            'data' => [
                'driver' => (string) config('mail.default', 'log'),
                'recipient' => $to,
                'sent' => (bool) $sent,
            ],
        ], $sent ? 200 : 422);
    }

    public function runtimeReady(): Response
    {
        /** @var RuntimeDiagnostics $diagnostics */
        $diagnostics = app(RuntimeDiagnostics::class);
        $payload = $diagnostics->readinessPayload();
        $status = (string) ($payload['status'] ?? 'degraded');

        return Response::json($payload, $status === 'healthy' ? 200 : 503);
    }

    public function runtimeSelfCheck(): Response
    {
        /** @var RuntimeDiagnostics $diagnostics */
        $diagnostics = app(RuntimeDiagnostics::class);
        return Response::json($diagnostics->runSelfCheck());
    }

    public function runtimeHistory(): Response
    {
        /** @var RuntimeDiagnostics $diagnostics */
        $diagnostics = app(RuntimeDiagnostics::class);
        return Response::json($diagnostics->historyPayload());
    }

    /**
     * @return array{class:string, exit_code:int, output:string}
     */
    private function runCommand(string $commandClass, array $args): array
    {
        /** @var \Core\Console\Command $command */
        $command = app()->make($commandClass);
        $command->setInput(array_merge(['framework'], $args));

        ob_start();
        $exitCode = $command->handle();
        $output = (string) ob_get_clean();

        return [
            'class' => $commandClass,
            'exit_code' => $exitCode,
            'output' => $output,
        ];
    }

    /**
     * @return array<int, array{name:string, has_web_routes:bool, has_api_routes:bool}>
     */
    private function discoverModules(): array
    {
        $modulesPath = base_path('modules');
        if (!is_dir($modulesPath)) {
            return [];
        }

        $rows = [];
        foreach (glob($modulesPath . '/*', GLOB_ONLYDIR) ?: [] as $path) {
            $name = basename($path);
            $rows[] = [
                'name' => $name,
                'has_web_routes' => is_file($path . '/routes/web.php'),
                'has_api_routes' => is_file($path . '/routes/api.php'),
            ];
        }

        usort($rows, static fn (array $a, array $b): int => strcmp($a['name'], $b['name']));

        return $rows;
    }

    /**
     * @return array<int, array{key:string, value:string}>
     */
    private function readEnvMasked(): array
    {
        $envPath = base_path('.env');
        if (!is_file($envPath)) {
            return [];
        }

        $rows = [];
        $lines = file($envPath, FILE_IGNORE_NEW_LINES) ?: [];
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '' || str_starts_with($trimmed, '#') || !str_contains($trimmed, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $trimmed, 2);
            $key = trim($key);
            $value = trim($value);
            if ($key === '') {
                continue;
            }

            $rows[] = [
                'key' => $key,
                'value' => $this->maskEnvValue($key, $value),
            ];
        }

        return $rows;
    }

    private function maskEnvValue(string $key, string $value): string
    {
        $sensitiveHints = ['KEY', 'SECRET', 'PASSWORD', 'TOKEN'];
        foreach ($sensitiveHints as $hint) {
            if (str_contains(strtoupper($key), $hint)) {
                if ($value === '') {
                    return '';
                }

                $length = strlen($value);
                if ($length <= 6) {
                    return str_repeat('*', $length);
                }

                return substr($value, 0, 3) . str_repeat('*', max(4, $length - 6)) . substr($value, -3);
            }
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function render(string $view, array $data = []): string
    {
        extract($data, EXTR_SKIP);
        ob_start();
        require __DIR__ . '/templates/' . $view . '.php';
        return (string) ob_get_clean();
    }
}
