<?php

use GeekCache\Cache\MemcachedCache;

class MemcachedIncrementableCacheTest extends BaseIncrementableCacheTestAbstract
{
    public function setUp(): void
    {
        parent::setUp();
        $memcached = new Memcached();
        $memcached->addServer('localhost', 11211);
        $memcached->flush();
        $memcachedCache = new MemcachedCache($memcached);
        $this->cache = new GeekCache\Cache\IncrementableStageableCache($memcachedCache);
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
