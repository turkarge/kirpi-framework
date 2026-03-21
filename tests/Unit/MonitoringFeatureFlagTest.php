<?php

declare(strict_types=1);

namespace Tests\Unit;

use Core\Http\Request;
use Core\Routing\Router;
use PHPUnit\Framework\TestCase;

class MonitoringFeatureFlagTest extends TestCase
{
    public function test_monitor_routes_are_not_registered_when_feature_is_disabled(): void
    {
        $previous = $_ENV['KIRPI_FEATURE_MONITORING'] ?? null;
        $_ENV['KIRPI_FEATURE_MONITORING'] = 'false';
        putenv('KIRPI_FEATURE_MONITORING=false');

        try {
            $router = new Router();
            $router->loadRoutes(BASE_PATH . '/routes/web.php', ['middleware' => 'web']);

            $response = $router->dispatch(new Request(server: [
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => '/kirpi-monitor',
                'HTTP_HOST' => 'localhost',
            ]));

            $this->assertSame(404, $response->getStatus());
        } finally {
            if ($previous === null) {
                unset($_ENV['KIRPI_FEATURE_MONITORING']);
                putenv('KIRPI_FEATURE_MONITORING');
            } else {
                $_ENV['KIRPI_FEATURE_MONITORING'] = $previous;
                putenv('KIRPI_FEATURE_MONITORING=' . $previous);
            }
        }
    }
}