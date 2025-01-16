<?php
namespace ULC_Sitemap\Core;

class Monitor {
    private const PERFORMANCE_LOG_KEY = 'ulc_sitemap_performance_log';
    private const MAX_LOG_ENTRIES = 100;
    private float $start_time;
    private float $start_memory;

    public function start_monitoring(): void {
        $this->start_time = microtime(true);
        $this->start_memory = memory_get_usage();
    }

    public function end_monitoring(string $operation, array $metadata = []): array {
        $end_time = microtime(true);
        $end_memory = memory_get_usage();

        $metrics = [
            'operation' => $operation,
            'timestamp' => current_time('mysql'),
            'duration' => round(($end_time - $this->start_time) * 1000, 2), // in milliseconds
            'memory_usage' => round(($end_memory - $this->start_memory) / 1024 / 1024, 2), // in MB
            'peak_memory' => round(memory_get_peak_usage() / 1024 / 1024, 2), // in MB
            'metadata' => $metadata
        ];

        $this->log_metrics($metrics);
        return $metrics;
    }

    public function get_performance_report(): array {
        $logs = $this->get_logs();
        
        if (empty($logs)) {
            return [];
        }

        // Calculate averages
        $total_duration = 0;
        $total_memory = 0;
        $total_peak = 0;
        $count = count($logs);

        $operations = [];
        foreach ($logs as $log) {
            $total_duration += $log['duration'];
            $total_memory += $log['memory_usage'];
            $total_peak += $log['peak_memory'];

            // Group by operation
            $op = $log['operation'];
            if (!isset($operations[$op])) {
                $operations[$op] = [
                    'count' => 0,
                    'total_duration' => 0,
                    'total_memory' => 0
                ];
            }
            $operations[$op]['count']++;
            $operations[$op]['total_duration'] += $log['duration'];
            $operations[$op]['total_memory'] += $log['memory_usage'];
        }

        // Calculate operation-specific metrics
        $operation_metrics = [];
        foreach ($operations as $op => $data) {
            $operation_metrics[$op] = [
                'count' => $data['count'],
                'avg_duration' => round($data['total_duration'] / $data['count'], 2),
                'avg_memory' => round($data['total_memory'] / $data['count'], 2)
            ];
        }

        return [
            'overview' => [
                'total_operations' => $count,
                'avg_duration' => round($total_duration / $count, 2),
                'avg_memory_usage' => round($total_memory / $count, 2),
                'avg_peak_memory' => round($total_peak / $count, 2),
                'first_logged' => $logs[0]['timestamp'],
                'last_logged' => end($logs)['timestamp']
            ],
            'operations' => $operation_metrics,
            'recent_logs' => array_slice($logs, -10)
        ];
    }

    public function get_recommendations(): array {
        $report = $this->get_performance_report();
        $recommendations = [];

        if (empty($report)) {
            return [
                [
                    'type' => 'info',
                    'message' => __('Not enough performance data collected yet.', 'ulc-sitemap')
                ]
            ];
        }

        // Check average duration
        if ($report['overview']['avg_duration'] > 1000) {
            $recommendations[] = [
                'type' => 'warning',
                'message' => __('Sitemap generation is taking longer than recommended. Consider enabling caching.', 'ulc-sitemap')
            ];
        }

        // Check memory usage
        if ($report['overview']['avg_peak_memory'] > 64) {
            $recommendations[] = [
                'type' => 'warning',
                'message' => __('High memory usage detected. Consider increasing PHP memory limit or reducing sitemap size.', 'ulc-sitemap')
            ];
        }

        // Operation-specific recommendations
        foreach ($report['operations'] as $op => $metrics) {
            if ($metrics['avg_duration'] > 500) {
                $recommendations[] = [
                    'type' => 'info',
                    'message' => sprintf(
                        __('Operation "%s" is taking longer than expected (%.2f ms). Consider optimization.', 'ulc-sitemap'),
                        $op,
                        $metrics['avg_duration']
                    )
                ];
            }
        }

        // Cache recommendations
        if (!wp_using_ext_object_cache()) {
            $recommendations[] = [
                'type' => 'info',
                'message' => __('Consider using an object cache (Redis/Memcached) for better performance.', 'ulc-sitemap')
            ];
        }

        return $recommendations;
    }

    public function render_performance_dashboard(): void {
        $report = $this->get_performance_report();
        $recommendations = $this->get_recommendations();

        include ULC_SITEMAP_PLUGIN_DIR . 'admin/views/performance-dashboard.php';
    }

    private function log_metrics(array $metrics): void {
        $logs = $this->get_logs();
        $logs[] = $metrics;

        // Keep only the most recent entries
        if (count($logs) > self::MAX_LOG_ENTRIES) {
            $logs = array_slice($logs, -self::MAX_LOG_ENTRIES);
        }

        update_option(self::PERFORMANCE_LOG_KEY, $logs);
    }

    private function get_logs(): array {
        return get_option(self::PERFORMANCE_LOG_KEY, []);
    }

    public function clear_logs(): void {
        delete_option(self::PERFORMANCE_LOG_KEY);
    }

    public function export_logs(): array {
        $logs = $this->get_logs();
        $report = $this->get_performance_report();
        $recommendations = $this->get_recommendations();

        return [
            'logs' => $logs,
            'report' => $report,
            'recommendations' => $recommendations,
            'system_info' => [
                'php_version' => phpversion(),
                'wordpress_version' => get_bloginfo('version'),
                'memory_limit' => ini_get('memory_limit'),
                'max_execution_time' => ini_get('max_execution_time'),
                'object_cache_enabled' => wp_using_ext_object_cache(),
                'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
                'database_version' => mysqli_get_client_info()
            ]
        ];
    }

    public function get_performance_metrics(): array {
        global $wpdb;

        return [
            'database' => [
                'query_count' => $wpdb->num_queries,
                'query_time' => timer_stop(),
                'cached_queries' => $wpdb->num_queries - $wpdb->num_cached_queries
            ],
            'memory' => [
                'current' => round(memory_get_usage() / 1024 / 1024, 2),
                'peak' => round(memory_get_peak_usage() / 1024 / 1024, 2),
                'limit' => ini_get('memory_limit')
            ],
            'cache' => [
                'object_cache_enabled' => wp_using_ext_object_cache(),
                'page_cache_enabled' => defined('WP_CACHE') && WP_CACHE,
                'opcode_cache_enabled' => function_exists('opcache_get_status')
            ]
        ];
    }
}