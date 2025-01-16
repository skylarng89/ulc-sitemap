<?php
if (!defined('WPINC')) {
    die;
}
?>
<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <?php
    // Display recommendations
    if (!empty($recommendations)): ?>
    <div class="notice-container">
        <?php foreach ($recommendations as $rec): ?>
            <div class="notice notice-<?php echo esc_attr($rec['type']); ?> is-dismissible">
                <p><?php echo esc_html($rec['message']); ?></p>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="ulc-dashboard-grid">
        <!-- Overview Card -->
        <div class="ulc-card">
            <h2><?php esc_html_e('Performance Overview', 'ulc-sitemap'); ?></h2>
            <table class="widefat striped">
                <tbody>
                    <tr>
                        <th><?php esc_html_e('Total Operations', 'ulc-sitemap'); ?></th>
                        <td><?php echo esc_html($report['overview']['total_operations']); ?></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('Average Duration', 'ulc-sitemap'); ?></th>
                        <td><?php echo esc_html($report['overview']['avg_duration']); ?> ms</td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('Average Memory Usage', 'ulc-sitemap'); ?></th>
                        <td><?php echo esc_html($report['overview']['avg_memory_usage']); ?> MB</td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('Average Peak Memory', 'ulc-sitemap'); ?></th>
                        <td><?php echo esc_html($report['overview']['avg_peak_memory']); ?> MB</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Operation Metrics -->
        <div class="ulc-card">
            <h2><?php esc_html_e('Operation Metrics', 'ulc-sitemap'); ?></h2>
            <table class="widefat striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Operation', 'ulc-sitemap'); ?></th>
                        <th><?php esc_html_e('Count', 'ulc-sitemap'); ?></th>
                        <th><?php esc_html_e('Avg. Duration', 'ulc-sitemap'); ?></th>
                        <th><?php esc_html_e('Avg. Memory', 'ulc-sitemap'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($report['operations'] as $op => $metrics): ?>
                    <tr>
                        <td><?php echo esc_html($op); ?></td>
                        <td><?php echo esc_html($metrics['count']); ?></td>
                        <td><?php echo esc_html($metrics['avg_duration']); ?> ms</td>
                        <td><?php echo esc_html($metrics['avg_memory']); ?> MB</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Recent Logs -->
        <div class="ulc-card">
            <h2><?php esc_html_e('Recent Activity', 'ulc-sitemap'); ?></h2>
            <table class="widefat striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Time', 'ulc-sitemap'); ?></th>
                        <th><?php esc_html_e('Operation', 'ulc-sitemap'); ?></th>
                        <th><?php esc_html_e('Duration', 'ulc-sitemap'); ?></th>
                        <th><?php esc_html_e('Memory', 'ulc-sitemap'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($report['recent_logs'] as $log): ?>
                    <tr>
                        <td><?php echo esc_html($log['timestamp']); ?></td>
                        <td><?php echo esc_html($log['operation']); ?></td>
                        <td><?php echo esc_html($log['duration']); ?> ms</td>
                        <td><?php echo esc_html($log['memory_usage']); ?> MB</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- System Information -->
        <div class="ulc-card">
            <h2><?php esc_html_e('System Information', 'ulc-sitemap'); ?></h2>
            <table class="widefat striped">
                <tbody>
                    <tr>
                        <th><?php esc_html_e('PHP Version', 'ulc-sitemap'); ?></th>
                        <td><?php echo esc_html(phpversion()); ?></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('WordPress Version', 'ulc-sitemap'); ?></th>
                        <td><?php echo esc_html(get_bloginfo('version')); ?></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('Memory Limit', 'ulc-sitemap'); ?></th>
                        <td><?php echo esc_html(ini_get('memory_limit')); ?></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('Max Execution Time', 'ulc-sitemap'); ?></th>
                        <td><?php echo esc_html(ini_get('max_execution_time')); ?> seconds</td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('Object Cache', 'ulc-sitemap'); ?></th>
                        <td><?php echo wp_using_ext_object_cache() ? 
                            esc_html__('Enabled', 'ulc-sitemap') : 
                            esc_html__('Disabled', 'ulc-sitemap'); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Actions -->
        <div class="ulc-card">
            <h2><?php esc_html_e('Actions', 'ulc-sitemap'); ?></h2>
            <p>
                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=ulc_clear_performance_logs'), 'ulc_clear_logs')); ?>" 
                   class="button button-secondary">
                    <?php esc_html_e('Clear Performance Logs', 'ulc-sitemap'); ?>
                </a>
                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=ulc_export_performance_data'), 'ulc_export_data')); ?>" 
                   class="button button-primary">
                    <?php esc_html_e('Export Performance Data', 'ulc-sitemap'); ?>
                </a>
            </p>
        </div>
    </div>
</div>

<style>
.ulc-dashboard-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin: 20px 0;
}

.ulc-card {
    background: #fff;
    border: 1px solid #ccd0d4;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
    padding: 15px;
}

.ulc-card h2 {
    margin-top: 0;
    padding: 0;
    border-bottom: 1px solid #eee;
    padding-bottom: 10px;
}

.notice-container {
    margin: 20px 0;
}

@media screen and (max-width: 782px) {
    .ulc-dashboard-grid {
        grid-template-columns: 1fr;
    }
}
</style>
<?php
// Add chart.js if we want to add charts later
wp_enqueue_script('ulc-charts', 'https://cdn.jsdelivr.net/npm/chart.js', [], '4.4.0', true);
?>