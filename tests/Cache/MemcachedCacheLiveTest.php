<?php
class MemcacheCachedLiveTest extends BaseCacheTest
{
    public function setUp(): void
    {
        parent::setUp();
        $memcached = new Memcached();
        $memcached->addServer('localhost', 11211);
        $memcached->flush();
        $this->cache = new GeekCache\Cache\MemcachedCache($memcached);
    }

    /**
     * @group slowTests
     */
    public function testTtlInteger()
    {
        $this->cache->put(self::KEY, self::VALUE, 1);
        $this->assertEquals(self::VALUE, $this->cache->get(self::KEY));
        usleep(2100000);
        $this->assertFalse($this->cache->get(self::KEY));
    }
}
