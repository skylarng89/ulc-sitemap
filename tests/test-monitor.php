<?php
class Test_ULC_Sitemap_Monitor extends WP_UnitTestCase {
    private $monitor;

    public function setUp(): void {
        parent::setUp();
        $this->monitor = new ULC_Sitemap\Core\Monitor();
    }

    public function tearDown(): void {
        $this->monitor->clear_logs();
        parent::tearDown();
    }

    public function test_performance_monitoring(): void {
        $this->monitor->start_monitoring();
        usleep(100000); // Simulate work for 100ms
        $metrics = $this->monitor->end_monitoring('test_operation');

        $this->assertArrayHasKey('duration', $metrics);
        $this->assertArrayHasKey('memory_usage', $metrics);
        $this->assertArrayHasKey('peak_memory', $metrics);
        $this->assertGreaterThan(0, $metrics['duration']);
    }

    public function test_performance_report(): void {
        // Generate some test data
        for ($i = 0; $i < 3; $i++) {
            $this->monitor->start_monitoring();
            usleep(50000);
            $this->monitor->end_monitoring('test_operation');
        }

        $report = $this->monitor->get_performance_report();

        $this->assertArrayHasKey('overview', $report);
        $this->assertArrayHasKey('operations', $report);
        $this->assertArrayHasKey('recent_logs', $report);
        $this->assertEquals(3, $report['overview']['total_operations']);
    }

    public function test_recommendations(): void {
        // Simulate slow operation
        $this->monitor->start_monitoring();
        usleep(1100000); // > 1000ms to trigger warning
        $this->monitor->end_monitoring('slow_operation');

        $recommendations = $this->monitor->get_recommendations();

        $this->assertIsArray($recommendations);
        $this->assertGreaterThan(0, count($recommendations));
        
        $has_warning = false;
        foreach ($recommendations as $rec) {
            if ($rec['type'] === 'warning') {
                $has_warning = true;
                break;
            }
        }
        $this->assertTrue($has_warning);
    }

    public function test_performance_metrics(): void {
        $metrics = $this->monitor->get_performance_metrics();

        $this->assertArrayHasKey('database', $metrics);
        $this->assertArrayHasKey('memory', $metrics);
        $this->assertArrayHasKey('cache', $metrics);

        $this->assertArrayHasKey('query_count', $metrics['database']);
        $this->assertArrayHasKey('current', $metrics['memory']);
        $this->assertArrayHasKey('object_cache_enabled', $metrics['cache']);
    }

    public function test_log_export(): void {
        // Generate test data
        $this->monitor->start_monitoring();
        $this->monitor->end_monitoring('export_test');

        $export = $this->monitor->export_logs();

        $this->assertArrayHasKey('logs', $export);
        $this->assertArrayHasKey('report', $export);
        $this->assertArrayHasKey('recommendations', $export);
        $this->assertArrayHasKey('system_info', $export);
        
        $this->assertArrayHasKey('php_version', $export['system_info']);
        $this->assertArrayHasKey('wordpress_version', $export['system_info']);
    }
}