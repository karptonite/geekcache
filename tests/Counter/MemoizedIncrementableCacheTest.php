<?php
class MemoizedIncrementableCacheTest extends BaseIncrementableCacheTest
{
    private $primarycache;

    public function setUp(): void
    {
        parent::setUp();
        $this->primarycache = new GeekCache\Cache\IncrementableArrayCache;
        $this->memoizedcache = new GeekCache\Cache\ArrayCache;
        $this->cache = new GeekCache\Cache\IncrementableMemoizedCache($this->primarycache, $this->memoizedcache);
    }

    public function testMemoizedIncrementableCacheIncrementsThePrimary()
    {
        $this->cache->put(self::KEY, 3);
        $this->cache->increment(self::KEY, 2);
        $this->assertEquals(5, $this->primarycache->get(self::KEY));
    }

    public function testMemoizedIncrementableCacheIncrementsWhenItHasNotRead()
    {
        $this->primarycache->put(self::KEY, 3);
        $this->cache->increment(self::KEY, 2);
        $this->assertEquals(5, $this->cache->get(self::KEY));
    }
}
