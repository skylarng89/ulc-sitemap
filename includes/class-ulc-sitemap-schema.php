<?php
namespace ULC_Sitemap\Core;

class Schema {
    private const SCHEMA_TYPES = [
        'post' => 'Article',
        'page' => 'WebPage',
        'product' => 'Product',
        'person' => 'Person',
        'organization' => 'Organization'
    ];

    public function init(): void {
        add_action('wp_head', [$this, 'inject_schema_markup']);
        add_filter('ulc_sitemap_settings_fields', [$this, 'add_schema_settings']);
    }

    public function inject_schema_markup(): void {
        if (!is_singular()) {
            return;
        }

        $schema = $this->generate_schema();
        if (!empty($schema)) {
            printf(
                '<script type="application/ld+json">%s</script>' . PHP_EOL,
                wp_json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
            );
        }
    }

    private function generate_schema(): array {
        $post = get_post();
        $schema_type = $this->get_schema_type($post);

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => $schema_type,
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => get_permalink()
            ],
            'headline' => get_the_title(),
            'description' => $this->get_description(),
            'datePublished' => get_the_date('c'),
            'dateModified' => get_the_modified_date('c'),
            'url' => get_permalink()
        ];

        // Add image if available
        if (has_post_thumbnail()) {
            $image_data = wp_get_attachment_image_src(get_post_thumbnail_id(), 'full');
            if ($image_data) {
                $schema['image'] = [
                    '@type' => 'ImageObject',
                    'url' => $image_data[0],
                    'width' => $image_data[1],
                    'height' => $image_data[2]
                ];
            }
        }

        // Add author information
        $author_id = get_post_field('post_author', $post);
        $schema['author'] = [
            '@type' => 'Person',
            'name' => get_the_author_meta('display_name', $author_id),
            'url' => get_author_posts_url($author_id)
        ];

        // Add publisher information
        $schema['publisher'] = $this->get_publisher_schema();

        // Add breadcrumbs
        $schema['breadcrumb'] = $this->get_breadcrumbs_schema();

        return $this->filter_schema($schema);
    }

    private function get_schema_type(\WP_Post $post): string {
        $post_type = get_post_type($post);
        return self::SCHEMA_TYPES[$post_type] ?? 'Article';
    }

    private function get_description(): string {
        $excerpt = get_the_excerpt();
        if (empty($excerpt)) {
            $excerpt = wp_trim_words(get_the_content(), 25);
        }
        return wp_strip_all_tags($excerpt);
    }

    private function get_publisher_schema(): array {
        $options = get_option('ulc_sitemap_options');
        
        $publisher = [
            '@type' => 'Organization',
            'name' => get_bloginfo('name'),
            'url' => home_url()
        ];

        // Add logo if specified
        if (!empty($options['publisher_logo'])) {
            $logo_data = wp_get_attachment_image_src($options['publisher_logo'], 'full');
            if ($logo_data) {
                $publisher['logo'] = [
                    '@type' => 'ImageObject',
                    'url' => $logo_data[0],
                    'width' => $logo_data[1],
                    'height' => $logo_data[2]
                ];
            }
        }

        return $publisher;
    }

    private function get_breadcrumbs_schema(): array {
        $breadcrumbs = [
            '@type' => 'BreadcrumbList',
            'itemListElement' => []
        ];

        $position = 1;
        
        // Add home
        $breadcrumbs['itemListElement'][] = [
            '@type' => 'ListItem',
            'position' => $position++,
            'item' => [
                '@id' => home_url(),
                'name' => __('Home', 'ulc-sitemap')
            ]
        ];

        // Add category if exists
        $categories = get_the_category();
        if (!empty($categories)) {
            $category = $categories[0];
            $breadcrumbs['itemListElement'][] = [
                '@type' => 'ListItem',
                'position' => $position++,
                'item' => [
                    '@id' => get_category_link($category->term_id),
                    'name' => $category->name
                ]
            ];
        }

        // Add current page
        $breadcrumbs['itemListElement'][] = [
            '@type' => 'ListItem',
            'position' => $position,
            'item' => [
                '@id' => get_permalink(),
                'name' => get_the_title()
            ]
        ];

        return $breadcrumbs;
    }

    private function filter_schema(array $schema): array {
        return apply_filters('ulc_sitemap_schema_markup', $schema);
    }

    public function add_schema_settings(array $fields): array {
        $fields['schema_section'] = [
            'title' => __('Schema.org Settings', 'ulc-sitemap'),
            'fields' => [
                'publisher_logo' => [
                    'label' => __('Publisher Logo', 'ulc-sitemap'),
                    'type' => 'media',
                    'description' => __('Select a logo to represent your organization in search results', 'ulc-sitemap')
                ],
                'enable_breadcrumbs' => [
                    'label' => __('Enable Breadcrumbs Schema', 'ulc-sitemap'),
                    'type' => 'checkbox'
                ],
                'schema_types' => [
                    'label' => __('Custom Schema Types', 'ulc-sitemap'),
                    'type' => 'textarea',
                    'description' => __('Enter custom post type to schema type mappings (one per line, format: post_type:SchemaType)', 'ulc-sitemap')
                ]
            ]
        ];

        return $fields;
    }
}