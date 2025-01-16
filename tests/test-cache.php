<?php
class Test_ULC_Sitemap_Cache extends WP_UnitTestCase {
    private $cache;

    public function setUp(): void {
        parent::setUp();
        $this->cache = new ULC_Sitemap\Core\Cache();
    }

    public function tearDown(): void {
        $this->cache->clear_all_cache();
        parent::tearDown();
    }

    public function test_set_and_get_cache(): void {
        $type = 'test';
        $content = 'Test content';

        $this->cache->set_cache($type, $content);
        $cached_content = $this->cache->get_cache($type);

        $this->assertEquals($content, $cached_content);
    }

    public function test_delete_cache(): void {
        $type = 'test';
        $content = 'Test content';

        $this->cache->set_cache($type, $content);
        $this->cache->delete_cache($type);

        $this->assertNull($this->cache->get_cache($type));
    }

    public function test_clear_all_cache(): void {
        $this->cache->set_cache('test1', 'content1');
        $this->cache->set_cache('test2', 'content2');

        $this->cache->clear_all_cache();

        $this->assertNull($this->cache->get_cache('test1'));
        $this->assertNull($this->cache->get_cache('test2'));
    }

    public function test_cache_validation(): void {
        $type = 'test';
        
        $this->assertFalse($this->cache->is_cache_valid($type));
        
        $this->cache->validate_cache($type);
        $this->assertTrue($this->cache->is_cache_valid($type));
        
        $this->cache->invalidate_cache($type);
        $this->assertFalse($this->cache->is_cache_valid($type));
    }
}