<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\Support\TestCase;

class RuntimeApiContractTest extends TestCase
{
    public function test_self_check_endpoint_contract(): void
    {
        $response = $this->get('/kirpi/self-check');

        $this->assertResponseStatus($response, 200);

        $data = json_decode($response->getContent(), true);
        $this->assertIsArray($data);
        $this->assertMatchesFixture($data, 'self-check.contract.json');
    }

    public function test_self_check_history_endpoint_contract(): void
    {
        $this->get('/kirpi/self-check');
        $this->get('/kirpi/self-check');

        $response = $this->get('/kirpi/self-check/history');

        $this->assertResponseStatus($response, 200);

        $data = json_decode($response->getContent(), true);
        $this->assertIsArray($data);
        $this->assertNotEmpty($data['items'] ?? []);
        $this->assertMatchesFixture($data, 'self-check-history.contract.json');
    }

    public function test_ready_endpoint_contract(): void
    {
        $response = $this->get('/ready');
        $this->assertContains($response->getStatus(), [200, 503]);

        $data = json_decode($response->getContent(), true);
        $this->assertIsArray($data);
        $this->assertMatchesFixture($data, 'ready.contract.json');
    }

    private function assertMatchesFixture(array $actual, string $fixtureName): void
    {
        $fixturePath = BASE_PATH . '/tests/Fixtures/runtime/' . $fixtureName;
        $raw = file_get_contents($fixturePath);

        $this->assertNotFalse($raw, 'Fixture read failed: ' . $fixturePath);

        $expected = json_decode($raw, true);
        $this->assertIsArray($expected, 'Fixture decode failed: ' . $fixturePath);

        $this->matchValue($actual, $expected, 'root');
    }

    private function matchValue(mixed $actual, mixed $expected, string $path): void
    {
        if (is_string($expected)) {
            if ($this->matchToken($actual, $expected, $path)) {
                return;
            }

            $this->assertSame($expected, $actual, "Mismatch at {$path}");
            return;
        }

        if (is_array($expected)) {
            $this->assertIsArray($actual, "Expected array at {$path}");

            if (array_key_exists('__each__', $expected)) {
                foreach ($actual as $index => $item) {
                    $this->matchValue($item, $expected['__each__'], $path . '[' . $index . ']');
                }
                return;
            }

            if ($this->isAssoc($expected)) {
                $this->assertSame(
                    array_keys($expected),
                    array_keys($actual),
                    "Key mismatch at {$path}"
                );

                foreach ($expected as $key => $childExpected) {
                    $this->matchValue($actual[$key], $childExpected, $path . '.' . $key);
                }

                return;
            }

            $this->assertCount(count($expected), $actual, "List length mismatch at {$path}");

            foreach ($expected as $index => $childExpected) {
                $this->matchValue($actual[$index], $childExpected, $path . '[' . $index . ']');
            }

            return;
        }

        $this->assertSame($expected, $actual, "Mismatch at {$path}");
    }

    private function matchToken(mixed $actual, string $token, string $path): bool
    {
        if ($token === '__ANY_STRING__') {
            $this->assertIsString($actual, "Expected string at {$path}");
            return true;
        }

        if ($token === '__ANY_INT__') {
            $this->assertIsInt($actual, "Expected int at {$path}");
            return true;
        }

        if ($token === '__ANY_NUMBER__') {
            $this->assertTrue(is_int($actual) || is_float($actual), "Expected number at {$path}");
            return true;
        }

        if ($token === '__ANY_NULLABLE_NUMBER__') {
            $this->assertTrue($actual === null || is_int($actual) || is_float($actual), "Expected nullable number at {$path}");
            return true;
        }

        if ($token === '__ARRAY_OF_NUMBERS__') {
            $this->assertIsArray($actual, "Expected number array at {$path}");
            foreach ($actual as $index => $value) {
                $this->assertTrue(is_int($value) || is_float($value), "Expected numeric value at {$path}[{$index}]");
            }
            return true;
        }

        if ($token === '__DATETIME_YMD_HIS__') {
            $this->assertIsString($actual, "Expected datetime string at {$path}");
            $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $actual, "Invalid datetime format at {$path}");
            return true;
        }

        if (str_starts_with($token, '__ONE_OF:') && str_ends_with($token, '__')) {
            $values = explode('|', substr($token, 9, -2));
            $this->assertContains((string) $actual, $values, "Value not allowed at {$path}");
            return true;
        }

        return false;
    }

    private function isAssoc(array $array): bool
    {
        return array_keys($array) !== range(0, count($array) - 1);
    }
}
