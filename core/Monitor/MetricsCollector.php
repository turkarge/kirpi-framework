<?php

declare(strict_types=1);

namespace Core\Monitor;

use Core\Database\DatabaseManager;

class MetricsCollector
{
    public function __construct(
        private readonly DatabaseManager $db,
    ) {}

    public function collect(): array
    {
        return [
            'memory'      => $this->memoryMetrics(),
            'cpu'         => $this->cpuMetrics(),
            'requests'    => $this->requestMetrics(),
            'database'    => $this->databaseMetrics(),
            'php'         => $this->phpMetrics(),
        ];
    }

    private function memoryMetrics(): array
    {
        $used  = memory_get_usage(true);
        $peak  = memory_get_peak_usage(true);
        $limit = $this->parseMemoryLimit(ini_get('memory_limit'));

        return [
            'used'      => $this->formatBytes($used),
            'used_raw'  => $used,
            'peak'      => $this->formatBytes($peak),
            'limit'     => $this->formatBytes($limit),
            'limit_raw' => $limit,
            'pct'       => $limit > 0 ? round(($used / $limit) * 100, 1) : 0,
        ];
    }

    private function cpuMetrics(): array
    {
        $load = sys_getloadavg();

        return [
            '1min'  => $load[0] ?? 0,
            '5min'  => $load[1] ?? 0,
            '15min' => $load[2] ?? 0,
        ];
    }

    private function requestMetrics(): array
    {
        // Log dosyasından son istekleri say
        // En son log dosyasını bul
        $logFiles = glob(storage_path('logs/*-app.log'));
        $logFile  = !empty($logFiles) ? end($logFiles) : '';
        $count   = 0;

        if (file_exists($logFile)) {
            $content = file_get_contents($logFile);
            $count   = substr_count($content, date('Y-m-d'));
        }

        return [
            'today'       => $count,
            'start_time'  => date('Y-m-d H:i:s', (int) ($_SERVER['REQUEST_TIME'] ?? time())),
            'execution_ms'=> round((microtime(true) - KIRPI_START) * 1000, 2),
        ];
    }

    private function databaseMetrics(): array
    {
        try {
            $start   = microtime(true);
            $this->db->raw('SELECT 1');
            $latency = round((microtime(true) - $start) * 1000, 2);

            // Tablo sayısı
            $tables = $this->db->raw("SHOW TABLES");

            return [
                'status'      => 'connected',
                'latency_ms'  => $latency,
                'table_count' => count($tables),
                'driver'      => config('database.default', 'mysql'),
            ];
        } catch (\Throwable $e) {
            return [
                'status' => 'disconnected',
                'error'  => $e->getMessage(),
            ];
        }
    }

    private function phpMetrics(): array
    {
        return [
            'version'    => PHP_VERSION,
            'extensions' => count(get_loaded_extensions()),
            'os'         => PHP_OS,
            'sapi'       => PHP_SAPI,
            'int_size'   => PHP_INT_SIZE * 8 . '-bit',
        ];
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes <= 0) return '0 B';
        $units = ['B', 'KB', 'MB', 'GB'];
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