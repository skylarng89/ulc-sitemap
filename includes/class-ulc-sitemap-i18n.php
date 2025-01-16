<?php
namespace ULC_Sitemap\Core;

class I18n {
    public function load_plugin_textdomain(): void {
        load_plugin_textdomain(
            'ulc-sitemap',
            false,
            dirname(plugin_basename(ULC_SITEMAP_PLUGIN_DIR)) . '/languages/'
        );
    }

    public static function get_translatable_strings(): array {
        return [
            'sitemap_error' => __('Error generating sitemap', 'ulc-sitemap'),
            'settings_saved' => __('Settings saved successfully', 'ulc-sitemap'),
            'cache_cleared' => __('Sitemap cache cleared successfully', 'ulc-sitemap'),
            'regenerate_success' => __('Sitemap regenerated successfully', 'ulc-sitemap'),
            'invalid_request' => __('Invalid request', 'ulc-sitemap'),
            'no_permission' => __('You do not have sufficient permissions', 'ulc-sitemap'),
        ];
    }
}