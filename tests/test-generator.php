<?php
class Test_ULC_Sitemap_Generator extends WP_UnitTestCase {
    private $generator;
    private $test_post;
    private $test_page;

    public function setUp(): void {
        parent::setUp();
        
        $this->generator = new ULC_Sitemap\Core\Generator();
        
        // Create test content
        $this->test_post = $this->factory->post->create([
            'post_title' => 'Test Post',
            'post_type' => 'post',
            'post_status' => 'publish'
        ]);
        
        $this->test_page = $this->factory->post->create([
            'post_title' => 'Test Page',
            'post_type' => 'page',
            'post_status' => 'publish'
        ]);
    }

    public function tearDown(): void {
        wp_delete_post($this->test_post, true);
        wp_delete_post($this->test_page, true);
        parent::tearDown();
    }

    public function test_generate_index(): void {
        ob_start();
        $this->generator->generate('index');
        $output = ob_get_clean();

        $this->assertStringContainsString('<?xml version="1.0" encoding="UTF-8"?>', $output);
        $this->assertStringContainsString('<sitemapindex', $output);
        $this->assertStringContainsString('</sitemapindex>', $output);
    }

    public function test_generate_post_sitemap(): void {
        ob_start();
        $this->generator->generate('post');
        $output = ob_get_clean();

        $this->assertStringContainsString('<?xml version="1.0" encoding="UTF-8"?>', $output);
        $this->assertStringContainsString('<urlset', $output);
        $this->assertStringContainsString(get_permalink($this->test_post), $output);
    }

    public function test_generate_page_sitemap(): void {
        ob_start();
        $this->generator->generate('page');
        $output = ob_get_clean();

        $this->assertStringContainsString('<?xml version="1.0" encoding="UTF-8"?>', $output);
        $this->assertStringContainsString('<urlset', $output);
        $this->assertStringContainsString(get_permalink($this->test_page), $output);
    }

    public function test_invalid_sitemap_type(): void {
        $this->expectException('WPDieException');
        $this->generator->generate('invalid_type');
    }

    public function test_sitemap_headers(): void {
        ob_start();
        $this->generator->generate('index');
        $headers = xdebug_get_headers();
        ob_end_clean();

        $this->assertContains('Content-Type: application/xml; charset=UTF-8', $headers);
    }
}