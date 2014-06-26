<?php
class MemcacheIncrementableCacheTest extends BaseIncrementableCacheTest
{
    public function setUp()
    {
        parent::setUp();
        $memcache = new Memcache();
        $memcache->connect('localhost', 11211);
        $memcache->flush();
        $this->cache = new GeekCache\Cache\MemcacheIncrementableCache($memcache);
    }
}
