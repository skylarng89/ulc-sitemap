<?php
namespace ULC_Sitemap\Core;

class Generator {
    private array $supported_types = ['index', 'post', 'page', 'category', 'tag', 'author'];
    private const ITEMS_PER_SITEMAP = 2000;

    public function generate(string $type): void {
        if (!in_array($type, $this->supported_types, true)) {
            wp_die('Invalid sitemap type');
        }

        header('Content-Type: application/xml; charset=UTF-8');
        if ($type === 'index') {
            echo $this->generate_index();
        } else {
            echo $this->generate_sitemap($type);
        }
    }

    private function generate_index(): string {
        $output = $this->get_xml_header();
        $output .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;

        foreach ($this->supported_types as $type) {
            if ($type === 'index') {
                continue;
            }

            $count = $this->get_type_count($type);
            $max_pages = ceil($count / self::ITEMS_PER_SITEMAP);

            for ($i = 1; $i <= $max_pages; $i++) {
                $output .= $this->get_sitemap_index_url($type, $i);
            }
        }

        $output .= '</sitemapindex>';
        return $output;
    }

    private function generate_sitemap(string $type): string {
        $output = $this->get_xml_header();
        $output .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
                           xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                           xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
                           http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">' . PHP_EOL;

        $items = $this->get_items($type);
        foreach ($items as $item) {
            $output .= $this->get_url_entry($item);
        }

        $output .= '</urlset>';
        return $output;
    }

    private function get_items(string $type): array {
        $items = [];
        $page = max(1, get_query_var('paged'));
        $offset = ($page - 1) * self::ITEMS_PER_SITEMAP;

        switch ($type) {
            case 'post':
                $items = get_posts([
                    'posts_per_page' => self::ITEMS_PER_SITEMAP,
                    'offset' => $offset,
                    'post_type' => 'post',
                    'post_status' => 'publish',
                    'orderby' => 'modified',
                    'order' => 'DESC',
                ]);
                break;

            case 'page':
                $items = get_pages([
                    'number' => self::ITEMS_PER_SITEMAP,
                    'offset' => $offset,
                    'post_status' => 'publish',
                ]);
                break;

            case 'category':
                $items = get_categories([
                    'number' => self::ITEMS_PER_SITEMAP,
                    'offset' => $offset,
                    'hide_empty' => true,
                ]);
                break;

            case 'tag':
                $items = get_tags([
                    'number' => self::ITEMS_PER_SITEMAP,
                    'offset' => $offset,
                    'hide_empty' => true,
                ]);
                break;

            case 'author':
                $items = get_users([
                    'number' => self::ITEMS_PER_SITEMAP,
                    'offset' => $offset,
                    'has_published_posts' => true,
                ]);
                break;
        }

        return $items;
    }

    private function get_type_count(string $type): int {
        return match ($type) {
            'post' => wp_count_posts('post')->publish,
            'page' => wp_count_posts('page')->publish,
            'category' => wp_count_terms('category', ['hide_empty' => true]),
            'tag' => wp_count_terms('post_tag', ['hide_empty' => true]),
            'author' => count_users()['total_users'],
            default => 0,
        };
    }

    private function get_url_entry(object $item): string {
        $url = $this->get_item_url($item);
        $modified = $this->get_item_modified($item);
        $priority = $this->get_item_priority($item);
        $changefreq = $this->get_item_changefreq($item);

        return sprintf(
            "\t<url>\n\t\t<loc>%s</loc>\n\t\t<lastmod>%s</lastmod>\n\t\t<changefreq>%s</changefreq>\n\t\t<priority>%.1f</priority>\n\t</url>\n",
            esc_url($url),
            esc_html($modified),
            esc_html($changefreq),
            (float) $priority
        );
    }

    private function get_item_url(object $item): string {
        if ($item instanceof \WP_Post) {
            return get_permalink($item);
        }
        if ($item instanceof \WP_Term) {
            return get_term_link($item);
        }
        if ($item instanceof \WP_User) {
            return get_author_posts_url($item->ID);
        }
        return '';
    }

    private function get_item_modified(object $item): string {
        if ($item instanceof \WP_Post) {
            return get_post_modified_time('c', true, $item);
        }
        return current_time('c', true);
    }

    private function get_item_priority(object $item): float {
        if ($item instanceof \WP_Post) {
            if ($item->post_type === 'page') {
                return 0.8;
            }
            $days_old = (time() - strtotime($item->post_date)) / DAY_IN_SECONDS;
            return min(1.0, max(0.1, 1.0 - ($days_old / 365)));
        }
        return 0.5;
    }

    private function get_item_changefreq(object $item): string {
        if ($item instanceof \WP_Post) {
            $days_old = (time() - strtotime($item->post_date)) / DAY_IN_SECONDS;
            if ($days_old < 7) return 'daily';
            if ($days_old < 30) return 'weekly';
            if ($days_old < 365) return 'monthly';
            return 'yearly';
        }
        return 'weekly';
    }

    private function get_sitemap_index_url(string $type, int $page): string {
        $url = home_url("sitemap-{$type}.xml");
        if ($page > 1) {
            $url = add_query_arg('paged', $page, $url);
        }
        return sprintf(
            "\t<sitemap>\n\t\t<loc>%s</loc>\n\t\t<lastmod>%s</lastmod>\n\t</sitemap>\n",
            esc_url($url),
            esc_html(current_time('c', true))
        );
    }

    private function get_xml_header(): string {
        return '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
    }
}