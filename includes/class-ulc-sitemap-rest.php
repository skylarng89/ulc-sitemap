<?php
namespace ULC_Sitemap\Core;

class REST {
    private const NAMESPACE = 'ulc-sitemap/v1';
    private Generator $generator;
    private Cache $cache;

    public function __construct(Generator $generator, Cache $cache) {
        $this->generator = $generator;
        $this->cache = $cache;
    }

    public function register_routes(): void {
        register_rest_route(
            self::NAMESPACE,
            '/sitemap/(?P<type>[a-zA-Z0-9-]+)',
            [
                'methods' => 'GET',
                'callback' => [$this, 'get_sitemap'],
                'permission_callback' => '__return_true',
                'args' => [
                    'type' => [
                        'required' => true,
                        'type' => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ]
                ]
            ]
        );

        register_rest_route(
            self::NAMESPACE,
            '/stats',
            [
                'methods' => 'GET',
                'callback' => [$this, 'get_stats'],
                'permission_callback' => [$this, 'check_admin_permission']
            ]
        );

        register_rest_route(
            self::NAMESPACE,
            '/regenerate',
            [
                'methods' => 'POST',
                'callback' => [$this, 'regenerate_sitemap'],
                'permission_callback' => [$this, 'check_admin_permission']
            ]
        );
    }

    public function get_sitemap(\WP_REST_Request $request): \WP_REST_Response {
        $type = $request->get_param('type');
        
        // Check cache first
        $cached_content = $this->cache->get_cache($type);
        if ($cached_content !== null) {
            return new \WP_REST_Response(
                $cached_content,
                200,
                ['Content-Type' => 'application/xml; charset=UTF-8']
            );
        }

        // Generate fresh sitemap
        ob_start();
        $this->generator->generate($type);
        $content = ob_get_clean();

        // Cache the result
        $this->cache->set_cache($type, $content);

        return new \WP_REST_Response(
            $content,
            200,
            ['Content-Type' => 'application/xml; charset=UTF-8']
        );
    }

    public function get_stats(): \WP_REST_Response {
        global $wpdb;

        $stats = [
            'total_urls' => 0,
            'last_modified' => get_option('ulc_sitemap_last_modified'),
            'cache_status' => wp_using_ext_object_cache(),
            'types' => []
        ];

        // Get counts for different content types
        $post_types = get_post_types(['public' => true]);
        foreach ($post_types as $type) {
            $count = wp_count_posts($type);
            $stats['types'][$type] = $count->publish;
            $stats['total_urls'] += $count->publish;
        }

        // Add taxonomy counts
        $taxonomies = get_taxonomies(['public' => true]);
        foreach ($taxonomies as $tax) {
            $count = wp_count_terms($tax, ['hide_empty' => true]);
            $stats['types'][$tax] = $count;
            $stats['total_urls'] += $count;
        }

        // Add cache info
        $stats['cache'] = [
            'enabled' => wp_using_ext_object_cache(),
            'size' => $this->get_cache_size(),
            'last_cleared' => get_option('ulc_sitemap_cache_last_cleared')
        ];

        return new \WP_REST_Response($stats);
    }

    public function regenerate_sitemap(\WP_REST_Request $request): \WP_REST_Response {
        // Clear existing cache
        $this->cache->clear_all_cache();

        // Generate new sitemaps
        $types = ['index', 'post', 'page', 'category', 'tag', 'author'];
        $generated = [];

        foreach ($types as $type) {
            ob_start();
            $this->generator->generate($type);
            $content = ob_get_clean();
            $this->cache->set_cache($type, $content);
            $generated[] = $type;
        }

        // Update last modified time
        update_option('ulc_sitemap_last_modified', current_time('mysql'));

        // Ping search engines
        do_action('ulc_sitemap_updated');

        return new \WP_REST_Response([
            'success' => true,
            'message' => __('Sitemap regenerated successfully', 'ulc-sitemap'),
            'generated' => $generated
        ]);
    }

    public function check_admin_permission(): bool {
        return current_user_can('manage_options');
    }

    private function get_cache_size(): int {
        global $wpdb;

        $size = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT SUM(LENGTH(option_value))
                FROM {$wpdb->options}
                WHERE option_name LIKE %s",
                $wpdb->esc_like('_transient_ulc_sitemap_') . '%'
            )
        );

        return (int)$size;
    }
}