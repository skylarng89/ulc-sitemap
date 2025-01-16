<?php
class Test_ULC_Sitemap_Validator extends WP_UnitTestCase {
    private $validator;
    
    public function setUp(): void {
        parent::setUp();
        $this->validator = new ULC_Sitemap\Core\Validator();
    }

    public function test_sitemap_validation_success(): void {
        $valid_sitemap = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL .
            '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL .
            '<url><loc>https://example.com/</loc></url>' . PHP_EOL .
            '</urlset>';

        $errors = $this->validator->validate_sitemap('urlset', $valid_sitemap);
        $this->assertEmpty($errors);
    }

    public function test_sitemap_validation_failure(): void {
        $invalid_sitemap = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL .
            '<urlset>' . PHP_EOL .
            '<invalid>https://example.com/</invalid>' . PHP_EOL .
            '</urlset>';

        $errors = $this->validator->validate_sitemap('urlset', $invalid_sitemap);
        $this->assertNotEmpty($errors);
    }

    public function test_url_health_check(): void {
        $urls = [
            home_url(),
            home_url('/nonexistent-page')
        ];

        $results = $this->validator->check_urls_health($urls);

        $this->assertArrayHasKey($urls[0], $results);
        $this->assertArrayHasKey($urls[1], $results);
        $this->assertEquals('ok', $results[$urls[0]]['status']);
        $this->assertEquals('error', $results[$urls[1]]['status']);
    }
}