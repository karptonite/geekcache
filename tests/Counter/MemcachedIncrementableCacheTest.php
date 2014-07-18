<?php
class MemcachedIncrementableCacheTest extends BaseIncrementableCacheTest
{
    public function setUp()
    {
        parent::setUp();
        $memcached = new Memcached();
        $memcached->addServer('localhost', 11211);
        $memcached->flush();
        $this->cache = new GeekCache\Cache\IncrementableMemcachedCache($memcached);
    }
}
