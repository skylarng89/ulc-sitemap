<?php
namespace ULC_Sitemap\Core;

class SEO {
    private const SEARCH_ENGINES = [
        'google' => [
            'name' => 'Google',
            'ping_url' => 'https://www.google.com/ping?sitemap=%s',
            'verify_meta' => 'google-site-verification',
            'webmaster_url' => 'https://search.google.com/search-console'
        ],
        'bing' => [
            'name' => 'Bing',
            'ping_url' => 'https://www.bing.com/ping?sitemap=%s',
            'verify_meta' => 'msvalidate.01',
            'webmaster_url' => 'https://www.bing.com/webmasters'
        ],
        'yandex' => [
            'name' => 'Yandex',
            'ping_url' => 'https://webmaster.yandex.com/ping?sitemap=%s',
            'verify_meta' => 'yandex-verification',
            'webmaster_url' => 'https://webmaster.yandex.com'
        ],
        'baidu' => [
            'name' => 'Baidu',
            'ping_url' => 'http://ping.baidu.com/ping/RPC2',
            'verify_meta' => 'baidu-site-verification',
            'webmaster_url' => 'https://ziyuan.baidu.com/site/'
        ]
    ];

    private const SOCIAL_META = [
        'facebook' => [
            'og:title' => ['property' => 'og:title', 'content' => '%s'],
            'og:description' => ['property' => 'og:description', 'content' => '%s'],
            'og:image' => ['property' => 'og:image', 'content' => '%s'],
            'og:url' => ['property' => 'og:url', 'content' => '%s'],
            'og:type' => ['property' => 'og:type', 'content' => 'website'],
            'fb:app_id' => ['property' => 'fb:app_id', 'content' => '%s']
        ],
        'twitter' => [
            'twitter:card' => ['name' => 'twitter:card', 'content' => 'summary_large_image'],
            'twitter:title' => ['name' => 'twitter:title', 'content' => '%s'],
            'twitter:description' => ['name' => 'twitter:description', 'content' => '%s'],
            'twitter:image' => ['name' => 'twitter:image', 'content' => '%s'],
            'twitter:site' => ['name' => 'twitter:site', 'content' => '@%s']
        ]
    ];

    public function init(): void {
        add_action('ulc_sitemap_updated', [$this, 'ping_search_engines']);
        add_action('wp_head', [$this, 'add_verification_meta']);
        add_action('wp_head', [$this, 'add_social_meta']);
        add_filter('ulc_sitemap_settings_fields', [$this, 'add_seo_settings']);
    }

    public function ping_search_engines(): void {
        $sitemap_url = home_url('sitemap.xml');
        
        foreach (self::SEARCH_ENGINES as $key => $engine) {
            if ($this->should_ping_engine($key)) {
                if ($key === 'baidu') {
                    $this->ping_baidu($sitemap_url);
                } else {
                    wp_remote_get(
                        sprintf($engine['ping_url'], urlencode($sitemap_url)),
                        ['blocking' => false]
                    );
                }
            }
        }
    }

    private function ping_baidu(string $sitemap_url): void {
        $options = get_option('ulc_sitemap_options');
        if (empty($options['baidu_token'])) {
            return;
        }

        $xml = xmlrpc_encode_request(
            'weblogUpdates.ping',
            [get_bloginfo('name'), home_url(), $sitemap_url]
        );

        wp_remote_post(
            self::SEARCH_ENGINES['baidu']['ping_url'],
            [
                'headers' => [
                    'Content-Type' => 'text/xml',
                    'Token' => $options['baidu_token']
                ],
                'body' => $xml,
                'blocking' => false
            ]
        );
    }

    public function add_verification_meta(): void {
        $options = get_option('ulc_sitemap_options');

        foreach (self::SEARCH_ENGINES as $key => $engine) {
            $meta_key = "{$key}_verify";
            if (!empty($options[$meta_key])) {
                printf(
                    '<meta name="%s" content="%s" />' . PHP_EOL,
                    esc_attr($engine['verify_meta']),
                    esc_attr($options[$meta_key])
                );
            }
        }
    }

    public function add_social_meta(): void {
        if (!is_singular()) {
            return;
        }

        $options = get_option('ulc_sitemap_options');
        $post = get_post();
        
        // Get post data
        $title = get_the_title();
        $description = wp_trim_words(get_the_excerpt(), 20);
        $image = get_the_post_thumbnail_url(get_the_ID(), 'large');
        $url = get_permalink();

        // Facebook meta tags
        if (!empty($options['enable_facebook_meta'])) {
            foreach (self::SOCIAL_META['facebook'] as $key => $meta) {
                $content = match ($key) {
                    'og:title' => $title,
                    'og:description' => $description,
                    'og:image' => $image,
                    'og:url' => $url,
                    'fb:app_id' => $options['facebook_app_id'] ?? '',
                    default => $meta['content']
                };

                if (!empty($content)) {
                    printf(
                        '<meta property="%s" content="%s" />' . PHP_EOL,
                        esc_attr($meta['property']),
                        esc_attr($content)
                    );
                }
            }
        }

        // Twitter meta tags
        if (!empty($options['enable_twitter_meta'])) {
            foreach (self::SOCIAL_META['twitter'] as $key => $meta) {
                $content = match ($key) {
                    'twitter:title' => $title,
                    'twitter:description' => $description,
                    'twitter:image' => $image,
                    'twitter:site' => $options['twitter_username'] ?? '',
                    default => $meta['content']
                };

                if (!empty($content)) {
                    printf(
                        '<meta name="%s" content="%s" />' . PHP_EOL,
                        esc_attr($meta['name']),
                        esc_attr($content)
                    );
                }
            }
        }
    }

    public function add_seo_settings(array $fields): array {
        // Search Engine Settings
        $fields['seo_section'] = [
            'title' => __('Search Engine Settings', 'ulc-sitemap'),
            'fields' => []
        ];

        foreach (self::SEARCH_ENGINES as $key => $engine) {
            // Verification code field
            $fields['seo_section']['fields']["{$key}_verify"] = [
                'label' => sprintf(
                    __('%s Verification Code', 'ulc-sitemap'),
                    $engine['name']
                ),
                'type' => 'text',
                'description' => sprintf(
                    __('Enter your %s verification code. Find it in %s', 'ulc-sitemap'),
                    $engine['name'],
                    '<a href="' . esc_url($engine['webmaster_url']) . '" target="_blank">' . 
                    $engine['name'] . ' Webmaster Tools</a>'
                )
            ];

            // Ping option
            $fields['seo_section']['fields']["ping_{$key}"] = [
                'label' => sprintf(
                    __('Ping %s', 'ulc-sitemap'),
                    $engine['name']
                ),
                'type' => 'checkbox',
                'description' => sprintf(
                    __('Notify %s when sitemap is updated', 'ulc-sitemap'),
                    $engine['name']
                )
            ];
        }

        // Baidu specific settings
        $fields['seo_section']['fields']['baidu_token'] = [
            'label' => __('Baidu API Token', 'ulc-sitemap'),
            'type' => 'text',
            'description' => __('Required for Baidu sitemap submission', 'ulc-sitemap')
        ];

        // Social Media Settings
        $fields['social_section'] = [
            'title' => __('Social Media Settings', 'ulc-sitemap'),
            'fields' => [
                'enable_facebook_meta' => [
                    'label' => __('Enable Facebook Meta Tags', 'ulc-sitemap'),
                    'type' => 'checkbox'
                ],
                'facebook_app_id' => [
                    'label' => __('Facebook App ID', 'ulc-sitemap'),
                    'type' => 'text'
                ],
                'enable_twitter_meta' => [
                    'label' => __('Enable Twitter Meta Tags', 'ulc-sitemap'),
                    'type' => 'checkbox'
                ],
                'twitter_username' => [
                    'label' => __('Twitter Username', 'ulc-sitemap'),
                    'type' => 'text',
                    'description' => __('Enter without @ symbol', 'ulc-sitemap')
                ]
            ]
        ];

        return $fields;
    }

    private function should_ping_engine(string $engine): bool {
        $options = get_option('ulc_sitemap_options');
        return !empty($options["ping_{$engine}"]);
    }
}