<?php
namespace ULC_Sitemap\Core;

class Cache {
    private const CACHE_PREFIX = 'ulc_sitemap_';
    private const CACHE_EXPIRATION = DAY_IN_SECONDS;

    public function get_cache(string $type): ?string {
        return get_transient(self::CACHE_PREFIX . $type);
    }

    public function set_cache(string $type, string $content): bool {
        return set_transient(
            self::CACHE_PREFIX . $type,
            $content,
            self::CACHE_EXPIRATION
        );
    }

    public function delete_cache(string $type): bool {
        return delete_transient(self::CACHE_PREFIX . $type);
    }

    public function clear_all_cache(): void {
        global $wpdb;
        
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
                $wpdb->esc_like('_transient_' . self::CACHE_PREFIX) . '%'
            )
        );
    }

    public function maybe_regenerate_cache(): void {
        if (!wp_using_ext_object_cache()) {
            $this->clear_all_cache();
        }
    }

    public function is_cache_valid(string $type): bool {
        return (bool) get_transient(self::CACHE_PREFIX . $type . '_valid');
    }

    public function invalidate_cache(string $type): void {
        delete_transient(self::CACHE_PREFIX . $type . '_valid');
    }

    public function validate_cache(string $type): void {
        set_transient(
            self::CACHE_PREFIX . $type . '_valid',
            true,
            self::CACHE_EXPIRATION
        );
    }
}