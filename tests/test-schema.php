<?php
class Test_ULC_Sitemap_Schema extends WP_UnitTestCase {
    private $schema;
    private $test_post;

    public function setUp(): void {
        parent::setUp();
        $this->schema = new ULC_Sitemap\Core\Schema();
        
        // Create test post
        $this->test_post = $this->factory->post->create([
            'post_title' => 'Test Post',
            'post_content' => 'Test content for schema generation',
            'post_status' => 'publish'
        ]);

        // Set current post
        $GLOBALS['post'] = get_post($this->test_post);
    }

    public function tearDown(): void {
        wp_delete_post($this->test_post, true);
        unset($GLOBALS['post']);
        parent::tearDown();
    }

    public function test_schema_markup_structure(): void {
        ob_start();
        $this->schema->inject_schema_markup();
        $output = ob_get_clean();

        $this->assertStringContainsString('<script type="application/ld+json">', $output);
        $this->assertStringContainsString('"@context":"https://schema.org"', $output);
        
        $json = json_decode(strip_tags(str_replace(['<script type="application/ld+json">', '</script>'], '', $output)), true);
        
        $this->assertIsArray($json);
        $this->assertArrayHasKey('@type', $json);
        $this->assertArrayHasKey('mainEntityOfPage', $json);
        $this->assertArrayHasKey('headline', $json);
        $this->assertArrayHasKey('datePublished', $json);
        $this->assertArrayHasKey('dateModified', $json);
        $this->assertArrayHasKey('author', $json);
        $this->assertArrayHasKey('publisher', $json);
    }

    public function test_breadcrumbs_schema(): void {
        // Create test category
        $category_id = $this->factory->term->create([
            'taxonomy' => 'category',
            'name' => 'Test Category'
        ]);

        wp_set_post_categories($this->test_post, [$category_id]);

        ob_start();
        $this->schema->inject_schema_markup();
        $output = ob_get_clean();

        $json = json_decode(strip_tags(str_replace(['<script type="application/ld+json">', '</script>'], '', $output)), true);

        $this->assertArrayHasKey('breadcrumb', $json);
        $this->assertEquals('BreadcrumbList', $json['breadcrumb']['@type']);
        $this->assertCount(3, $json['breadcrumb']['itemListElement']); // Home, Category, Post
    }

    public function test_custom_schema_type(): void {
        // Add custom schema type
        add_filter('ulc_sitemap_schema_markup', function($schema) {
            $schema['@type'] = 'BlogPosting';
            return $schema;
        });

        ob_start();
        $this->schema->inject_schema_markup();
        $output = ob_get_clean();

        $json = json_decode(strip_tags(str_replace(['<script type="application/ld+json">', '</script>'], '', $output)), true);
        $this->assertEquals('BlogPosting', $json['@type']);
    }

    public function test_schema_settings(): void {
        $fields = [];
        $fields = $this->schema->add_schema_settings($fields);

        $this->assertArrayHasKey('schema_section', $fields);
        $this->assertArrayHasKey('fields', $fields['schema_section']);
        $this->assertArrayHasKey('publisher_logo', $fields['schema_section']['fields']);
        $this->assertArrayHasKey('enable_breadcrumbs', $fields['schema_section']['fields']);
    }
}