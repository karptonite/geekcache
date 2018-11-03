<?php
class MemcacheIncrementableCacheTest extends BaseIncrementableCacheTest
{
    public function setUp()
    {
        parent::setUp();
        $memcache = new Memcache();
        $memcache->connect('localhost', 11211);
        $memcache->flush();
        $this->cache = new GeekCache\Cache\IncrementableMemcacheCache($memcache);
    }

    /**
     * @group slowTests
     */
    public function testTimeout()
    {
        $this->cache->increment(static::KEY, 1, 1);
        usleep(2100000);
        $this->assertFalse($this->cache->get(static::KEY));
    }
}
