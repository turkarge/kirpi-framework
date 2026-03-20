<?php

declare(strict_types=1);

namespace Core\Monitor;

use Core\Database\DatabaseManager;
use Core\Cache\CacheManager;

class HealthChecker
{
    public function __construct(
        private readonly DatabaseManager $db,
        private readonly CacheManager    $cache,
    ) {}

    public function check(): array
{
    $checks = [
        'database' => $this->checkDatabase(),
        'cache'    => $this->checkCache(),
        'storage'  => $this->checkStorage(),
        'memory'   => $this->checkMemory(),
        'queue'    => $this->checkQueue(),
    ];

    $allHealthy  = empty(array_filter($checks, fn($c) => $c['status'] !== 'healthy'));
    $hasCritical = !empty(array_filter($checks, fn($c) => $c['status'] === 'critical'));

    $overallStatus = $allHealthy ? 'healthy' : ($hasCritical ? 'critical' : 'warning');

    return [
        'status'    => $overallStatus,
        'checks'    => $checks,
        'uptime'    => $this->getUptime(),
        'version'   => config('app.version', '1.0.0'),
        'php'       => PHP_VERSION,
        'env'       => config('app.env', 'local'),
        'timestamp' => date('Y-m-d H:i:s'),
    ];
}

    private function checkDatabase(): array
    {
        try {
            $start = microtime(true);
            $this->db->raw('SELECT 1');
            $latency = round((microtime(true) - $start) * 1000, 2);

            return [
                'status'  => 'healthy',
                'latency' => "{$latency}ms",
                'driver'  => config('database.default', 'mysql'),
            ];
        } catch (\Throwable $e) {
            return [
                'status' => 'critical',
                'error'  => $e->getMessage(),
            ];
        }
    }

    private function checkCache(): array
    {
        try {
            $key = 'kirpi_health_' . time();
            $this->cache->set($key, 'ok', 10);
            $value = $this->cache->get($key);
            $this->cache->delete($key);

            return [
                'status' => $value === 'ok' ? 'healthy' : 'warning',
                'driver' => config('cache.default', 'file'),
            ];
        } catch (\Throwable $e) {
            return [
                'status' => 'critical',
                'error'  => $e->getMessage(),
            ];
        }
    }

    private function checkStorage(): array
{
    $free  = disk_free_space(BASE_PATH);
    $total = disk_total_space(BASE_PATH);

    if ($free === false || $total === false) {
        return ['status' => 'warning', 'error' => 'Could not check disk space'];
    }

    $used = $total - $free;
    $pct  = round(($used / $total) * 100, 1);

    return [
        'status'   => $pct > 90 ? 'critical' : ($pct > 70 ? 'warning' : 'healthy'),
        'free'     => $this->formatBytes((int) $free),
        'total'    => $this->formatBytes((int) $total),
        'used_pct' => $pct,
    ];
}

    private function checkMemory(): array
    {
        $used  = memory_get_usage(true);
        $peak  = memory_get_peak_usage(true);
        $limit = $this->parseMemoryLimit(ini_get('memory_limit'));
        $pct   = $limit > 0 ? round(($used / $limit) * 100, 1) : 0;

        return [
            'status'     => $pct > 90 ? 'critical' : ($pct > 70 ? 'warning' : 'healthy'),
            'used'       => $this->formatBytes($used),
            'peak'       => $this->formatBytes($peak),
            'limit'      => $this->formatBytes($limit),
            'used_pct'   => $pct,
        ];
    }

    private function checkQueue(): array
    {
        try {
            $driver = config('queue.default', 'sync');

            if ($driver === 'sync') {
                return ['status' => 'healthy', 'driver' => 'sync', 'size' => 0];
            }

            $size = app(\Core\Queue\QueueManager::class)->size();

            return [
                'status' => $size > 1000 ? 'warning' : 'healthy',
                'driver' => $driver,
                'size'   => $size,
            ];
        } catch (\Throwable $e) {
            return ['status' => 'warning', 'error' => $e->getMessage()];
        }
    }

    private function getUptime(): string
    {
        if (file_exists('/proc/uptime')) {
            $uptime  = (int) file_get_contents('/proc/uptime');
            $days    = floor($uptime / 86400);
            $hours   = floor(($uptime % 86400) / 3600);
            $minutes = floor(($uptime % 3600) / 60);
            return "{$days}d {$hours}h {$minutes}m";
        }

        return 'N/A';
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes <= 0) return '0 B';
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i     = floor(log($bytes, 1024));
        return round($bytes / pow(1024, $i), 2) . ' ' . $units[$i];
    }

    private function parseMemoryLimit(string $limit): int
    {
        if ($limit === '-1') return PHP_INT_MAX;

        $unit  = strtolower(substr($limit, -1));
        $value = (int) $limit;

        return match($unit) {
            'g' => $value * 1024 * 1024 * 1024,
            'm' => $value * 1024 * 1024,
            'k' => $value * 1024,
            default => $value,
        };
    }
}