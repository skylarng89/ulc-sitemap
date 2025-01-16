<?php
namespace ULC_Sitemap\Core;

class Admin {
    public function init(): void {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        add_filter('plugin_action_links_' . plugin_basename(ULC_SITEMAP_PLUGIN_DIR . 'ulc-sitemap.php'), 
            [$this, 'add_settings_link']
        );
    }

    public function add_admin_menu(): void {
        add_options_page(
            __('ULC Sitemap Settings', 'ulc-sitemap'),
            __('ULC Sitemap', 'ulc-sitemap'),
            'manage_options',
            'ulc-sitemap',
            [$this, 'render_settings_page']
        );
    }

    public function register_settings(): void {
        register_setting('ulc_sitemap_options', 'ulc_sitemap_options', [
            'sanitize_callback' => [$this, 'sanitize_options'],
            'default' => [
                'exclude_post_types' => [],
                'exclude_taxonomies' => [],
                'priority_home' => '1.0',
                'priority_posts' => '0.8',
                'priority_pages' => '0.6',
                'priority_cats' => '0.5',
                'priority_tags' => '0.4',
                'priority_authors' => '0.3',
            ],
        ]);

        add_settings_section(
            'ulc_sitemap_main',
            __('Main Settings', 'ulc-sitemap'),
            [$this, 'render_section_main'],
            'ulc-sitemap'
        );

        add_settings_field(
            'exclude_post_types',
            __('Exclude Post Types', 'ulc-sitemap'),
            [$this, 'render_exclude_post_types'],
            'ulc-sitemap',
            'ulc_sitemap_main'
        );

        add_settings_field(
            'exclude_taxonomies',
            __('Exclude Taxonomies', 'ulc-sitemap'),
            [$this, 'render_exclude_taxonomies'],
            'ulc-sitemap',
            'ulc_sitemap_main'
        );

        // Add priority settings fields
        $priority_fields = [
            'home' => __('Homepage Priority', 'ulc-sitemap'),
            'posts' => __('Posts Priority', 'ulc-sitemap'),
            'pages' => __('Pages Priority', 'ulc-sitemap'),
            'cats' => __('Categories Priority', 'ulc-sitemap'),
            'tags' => __('Tags Priority', 'ulc-sitemap'),
            'authors' => __('Authors Priority', 'ulc-sitemap'),
        ];

        foreach ($priority_fields as $key => $label) {
            add_settings_field(
                "priority_$key",
                $label,
                [$this, 'render_priority_field'],
                'ulc-sitemap',
                'ulc_sitemap_main',
                ['key' => $key]
            );
        }
    }

    public function sanitize_options(array $input): array {
        $sanitized = [];
        
        // Sanitize arrays
        $sanitized['exclude_post_types'] = isset($input['exclude_post_types']) ? 
            array_map('sanitize_text_field', (array)$input['exclude_post_types']) : [];
            
        $sanitized['exclude_taxonomies'] = isset($input['exclude_taxonomies']) ? 
            array_map('sanitize_text_field', (array)$input['exclude_taxonomies']) : [];

        // Sanitize priorities
        $priority_fields = ['home', 'posts', 'pages', 'cats', 'tags', 'authors'];
        foreach ($priority_fields as $field) {
            $key = "priority_$field";
            $sanitized[$key] = isset($input[$key]) ? 
                min(1.0, max(0.0, (float)$input[$key])) : 0.5;
        }

        return $sanitized;
    }

    public function render_settings_page(): void {
        if (!current_user_can('manage_options')) {
            return;
        }

        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields('ulc_sitemap_options');
                do_settings_sections('ulc-sitemap');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    public function render_section_main(): void {
        echo '<p>' . esc_html__('Configure your sitemap settings below.', 'ulc-sitemap') . '</p>';
    }

    public function render_exclude_post_types(): void {
        $options = get_option('ulc_sitemap_options');
        $post_types = get_post_types(['public' => true], 'objects');

        foreach ($post_types as $post_type) {
            $checked = isset($options['exclude_post_types']) && 
                      in_array($post_type->name, $options['exclude_post_types'], true);
            ?>
            <label>
                <input type="checkbox" 
                       name="ulc_sitemap_options[exclude_post_types][]" 
                       value="<?php echo esc_attr($post_type->name); ?>"
                       <?php checked($checked); ?>>
                <?php echo esc_html($post_type->label); ?>
            </label><br>
            <?php
        }
    }

    public function render_exclude_taxonomies(): void {
        $options = get_option('ulc_sitemap_options');
        $taxonomies = get_taxonomies(['public' => true], 'objects');

        foreach ($taxonomies as $taxonomy) {
            $checked = isset($options['exclude_taxonomies']) && 
                      in_array($taxonomy->name, $options['exclude_taxonomies'], true);
            ?>
            <label>
                <input type="checkbox" 
                       name="ulc_sitemap_options[exclude_taxonomies][]" 
                       value="<?php echo esc_attr($taxonomy->name); ?>"
                       <?php checked($checked); ?>>
                <?php echo esc_html($taxonomy->label); ?>
            </label><br>
            <?php
        }
    }

    public function render_priority_field(array $args): void {
        $options = get_option('ulc_sitemap_options');
        $key = $args['key'];
        $value = $options["priority_$key"] ?? 0.5;
        ?>
        <input type="number" 
               name="ulc_sitemap_options[priority_<?php echo esc_attr($key); ?>]" 
               value="<?php echo esc_attr($value); ?>"
               step="0.1" 
               min="0" 
               max="1" 
               class="small-text">
        <?php
    }

    public function add_settings_link(array $links): array {
        $settings_link = sprintf(
            '<a href="%s">%s</a>',
            admin_url('options-general.php?page=ulc-sitemap'),
            __('Settings', 'ulc-sitemap')
        );
        array_unshift($links, $settings_link);
        return $links;
    }
}