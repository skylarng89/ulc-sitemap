<?php
namespace ULC_Sitemap\Core;

class Deactivator {
    public static function deactivate(): void {
        // Remove cron job
        $timestamp = wp_next_scheduled('ulc_sitemap_cron_regenerate');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'ulc_sitemap_cron_regenerate');
        }

        // Clear plugin-specific rewrite rules
        flush_rewrite_rules();

        // Cleanup transients if any were created
        delete_transient('ulc_sitemap_index_cache');
        delete_transient('ulc_sitemap_post_cache');
        delete_transient('ulc_sitemap_page_cache');
        delete_transient('ulc_sitemap_category_cache');
        delete_transient('ulc_sitemap_tag_cache');
        delete_transient('ulc_sitemap_author_cache');

        // Remove activation flag
        delete_option('ulc_sitemap_activated');
    }
}