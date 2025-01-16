<?php
namespace ULC_Sitemap\Core;

class Plugin {
    protected Generator $generator;
    protected Admin $admin;
    
    public function __construct() {
        $this->load_dependencies();
        $this->setup_actions();
    }
    
    private function load_dependencies(): void {
        $this->generator = new Generator();
        $this->admin = new Admin();
    }
    
    private function setup_actions(): void {
        // Add rewrite rules
        add_action('init', [$this, 'add_rewrite_rules']);
        
        // Handle sitemap requests
        add_action('template_redirect', [$this, 'handle_sitemap_request']);
        
        // Add sitemap to robots.txt
        add_filter('robots_txt', [$this, 'add_sitemap_to_robots'], 10, 2);
    }
    
    public function run(): void {
        $this->admin->init();
    }
    
    public function add_rewrite_rules(): void {
        add_rewrite_rule(
            'sitemap\.xml$',
            'index.php?ulc_sitemap=index',
            'top'
        );
        add_rewrite_rule(
            'sitemap-([^/]+)\.xml$',
            'index.php?ulc_sitemap=$matches[1]',
            'top'
        );
        
        add_rewrite_tag('%ulc_sitemap%', '([^/]+)');
    }
    
    public function handle_sitemap_request(): void {
        $sitemap_type = get_query_var('ulc_sitemap');
        
        if (!empty($sitemap_type)) {
            $this->generator->generate($sitemap_type);
            exit;
        }
    }
    
    public function add_sitemap_to_robots(string $output, bool $public): string {
        if ($public) {
            $output .= "\nSitemap: " . home_url('sitemap.xml');
        }
        return $output;
    }
}