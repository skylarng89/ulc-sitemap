<?php
namespace ULC_Sitemap\Core;

class Activator {
    public static function activate(): void {
        // Add default options if they don't exist
        if (!get_option('ulc_sitemap_options')) {
            add_option('ulc_sitemap_options', [
                'exclude_post_types' => [],
                'exclude_taxonomies' => [],
                'priority_home' => '1.0',
                'priority_posts' => '0.8',
                'priority_pages' => '0.6',
                'priority_cats' => '0.5',
                'priority_tags' => '0.4',
                'priority_authors' => '0.3',
            ]);
        }

        // Flush rewrite rules for sitemap URLs
        flush_rewrite_rules();

        // Set activation flag
        add_option('ulc_sitemap_activated', true);

        // Setup cron job for sitemap regeneration if needed
        if (!wp_next_scheduled('ulc_sitemap_cron_regenerate')) {
            wp_schedule_event(time(), 'daily', 'ulc_sitemap_cron_regenerate');
        }
    }
}