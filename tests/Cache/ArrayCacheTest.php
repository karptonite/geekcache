<?php
class ArrayCacheTest extends BaseCacheTest
{
    public function setUp()
    {
        parent::setUp();
        $this->cache = new GeekCache\Cache\ArrayCache;
    }

    public function testCacheSizeLimit()
    {
        $cache = new GeekCache\Cache\ArrayCache(2);
        $cache->put(self::KEY, self::VALUE);
        $resultAllowed = $cache->put(self::KEY2, self::VALUE2);
        $resultOverflow = $cache->put(self::KEY3, self::VALUE3);
        $cache->put(self::KEY2, self::VALUE);

        $this->assertTrue($resultAllowed);
        $this->assertFalse($resultOverflow);
        $this->assertEquals(self::VALUE, $cache->get(self::KEY));
        $this->assertFalse($cache->get(self::KEY3));
        $this->assertEquals(self::VALUE, $cache->get(self::KEY2));
    }
}
