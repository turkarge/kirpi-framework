<?php

declare(strict_types=1);

namespace Tests\Unit;

use Core\Http\Request;
use Core\Routing\Router;
use PHPUnit\Framework\TestCase;

class AiFeatureFlagTest extends TestCase
{
    public function test_ai_routes_are_not_registered_when_feature_is_disabled(): void
    {
        $previous = $_ENV['KIRPI_FEATURE_AI'] ?? null;
        $_ENV['KIRPI_FEATURE_AI'] = 'false';
        putenv('KIRPI_FEATURE_AI=false');

        try {
            $router = new Router();
            $router->loadRoutes(BASE_PATH . '/routes/web.php', ['middleware' => 'web']);

            $response = $router->dispatch(new Request(server: [
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => '/kirpi/ai-sql-test',
                'HTTP_HOST' => 'localhost',
            ]));

            $this->assertSame(404, $response->getStatus());
        } finally {
            if ($previous === null) {
                unset($_ENV['KIRPI_FEATURE_AI']);
                putenv('KIRPI_FEATURE_AI');
            } else {
                $_ENV['KIRPI_FEATURE_AI'] = $previous;
                putenv('KIRPI_FEATURE_AI=' . $previous);
            }
        }
    }
}
