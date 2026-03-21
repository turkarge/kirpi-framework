<?php

declare(strict_types=1);

namespace Core\Runtime;

use Core\Cache\CacheManager;
use Core\Database\DatabaseManager;

class RuntimeDiagnostics
{
    public function __construct(
        private readonly DatabaseManager $database,
        private readonly CacheManager $cache,
        private readonly string $historyPath = '',
    ) {}

    public function checks(): array
    {
        $dbStartedAt = microtime(true);
        $db = ['status' => 'down', 'message' => 'unreachable', 'latency_ms' => null];

        try {
            $this->database->raw('SELECT 1');
            $db = [
                'status' => 'up',
                'message' => 'ok',
                'latency_ms' => round((microtime(true) - $dbStartedAt) * 1000, 2),
            ];
        } catch (\Throwable $e) {
            $db = [
                'status' => 'down',
                'message' => $e->getMessage(),
                'latency_ms' => round((microtime(true) - $dbStartedAt) * 1000, 2),
            ];
        }

        $cacheStartedAt = microtime(true);
        $cache = ['status' => 'down', 'message' => 'unreachable', 'latency_ms' => null];

        try {
            $key = 'kirpi_runtime_check_' . bin2hex(random_bytes(4));
            $this->cache->set($key, 'ok', 10);
            $value = $this->cache->get($key);
            $this->cache->delete($key);

            $cache = [
                'status' => $value === 'ok' ? 'up' : 'down',
                'message' => $value === 'ok' ? 'ok' : 'read/write failed',
                'latency_ms' => round((microtime(true) - $cacheStartedAt) * 1000, 2),
            ];
        } catch (\Throwable $e) {
            $cache = [
                'status' => 'down',
                'message' => $e->getMessage(),
                'latency_ms' => round((microtime(true) - $cacheStartedAt) * 1000, 2),
            ];
        }

        return ['database' => $db, 'cache' => $cache];
    }

    public function overallStatus(array $checks): string
    {
        return ($checks['database']['status'] ?? null) === 'up' && ($checks['cache']['status'] ?? null) === 'up'
            ? 'healthy'
            : 'degraded';
    }

    public function readinessPayload(): array
    {
        $checks = $this->checks();
        $policy = $this->readinessPolicy();
        $reasons = $this->readinessReasons($checks, $policy);
        $status = $reasons === [] ? 'healthy' : 'degraded';

        return [
            'status' => $status,
            'checks' => $checks,
            'policy' => $policy,
            'reasons' => $reasons,
            'timestamp' => date('Y-m-d H:i:s'),
        ];
    }

    public function runSelfCheck(): array
    {
        $startedAt = microtime(true);
        $checks = $this->checks();

        $result = [
            'status' => $this->overallStatus($checks),
            'checks' => $checks,
            'took_ms' => round((microtime(true) - $startedAt) * 1000, 2),
            'timestamp' => date('Y-m-d H:i:s'),
        ];

        $this->appendHistory($result);
        $result['latency_trend'] = $this->buildLatencyTrend($this->loadHistory());

        return $result;
    }

    public function historyPayload(): array
    {
        $history = $this->loadHistory();

        return [
            'items' => $history,
            'latency_trend' => $this->buildLatencyTrend($history),
        ];
    }

    private function loadHistory(): array
    {
        $path = $this->resolvedHistoryPath();

        if (!file_exists($path)) {
            return [];
        }

        $raw = file_get_contents($path);

        if ($raw === false || $raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function saveHistory(array $history): void
    {
        $path = $this->resolvedHistoryPath();
        $dir = dirname($path);

        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        file_put_contents($path, json_encode($history, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    private function appendHistory(array $entry): void
    {
        $history = $this->loadHistory();
        array_unshift($history, $entry);
        $history = array_slice($history, 0, 20);
        $this->saveHistory($history);
    }

    private function buildLatencyTrend(array $history): array
    {
        $window = 10;
        $points = [];

        foreach (array_slice($history, 0, $window) as $item) {
            if (isset($item['took_ms']) && is_numeric($item['took_ms'])) {
                $points[] = (float) $item['took_ms'];
            }
        }

        if ($points === []) {
            return [
                'window' => $window,
                'points' => [],
                'avg_ms' => null,
                'min_ms' => null,
                'max_ms' => null,
                'last_ms' => null,
                'direction' => 'flat',
            ];
        }

        $avg = round(array_sum($points) / count($points), 2);
        $min = round(min($points), 2);
        $max = round(max($points), 2);
        $last = round($points[0], 2);
        $first = round($points[count($points) - 1], 2);
        $delta = round($last - $first, 2);
        $direction = 'flat';

        if ($delta > 2.0) {
            $direction = 'up';
        } elseif ($delta < -2.0) {
            $direction = 'down';
        }

        return [
            'window' => $window,
            'points' => $points,
            'avg_ms' => $avg,
            'min_ms' => $min,
            'max_ms' => $max,
            'last_ms' => $last,
            'direction' => $direction,
        ];
    }

    private function resolvedHistoryPath(): string
    {
        if ($this->historyPath !== '') {
            return $this->historyPath;
        }

        return storage_path('framework/self-check-history.json');
    }

    private function readinessPolicy(): array
    {
        $dbMax = (float) env('KIRPI_READY_MAX_DB_LATENCY_MS', 250);
        $cacheMax = (float) env('KIRPI_READY_MAX_CACHE_LATENCY_MS', 150);

        return [
            'requires' => ['database_up', 'cache_up'],
            'max_latency_ms' => [
                'database' => $dbMax,
                'cache' => $cacheMax,
            ],
        ];
    }

    private function readinessReasons(array $checks, array $policy): array
    {
        $reasons = [];

        if (($checks['database']['status'] ?? null) !== 'up') {
            $reasons[] = 'database_down';
        }

        if (($checks['cache']['status'] ?? null) !== 'up') {
            $reasons[] = 'cache_down';
        }

        $dbLatency = $checks['database']['latency_ms'] ?? null;
        $dbMax = (float) ($policy['max_latency_ms']['database'] ?? 0);
        if (is_numeric($dbLatency) && (float) $dbLatency > $dbMax) {
            $reasons[] = 'database_latency_exceeded';
        }

        $cacheLatency = $checks['cache']['latency_ms'] ?? null;
        $cacheMax = (float) ($policy['max_latency_ms']['cache'] ?? 0);
        if (is_numeric($cacheLatency) && (float) $cacheLatency > $cacheMax) {
            $reasons[] = 'cache_latency_exceeded';
        }

        return $reasons;
    }
}
