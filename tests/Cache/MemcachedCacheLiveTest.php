<?php

use GeekCache\Cache\MemcachedCache;
use GeekCache\Cache\NormalCacheItem;

class MemcachedCacheLiveTest extends BaseCacheTestAbstract
{
    public function setUp(): void
    {
        parent::setUp();
        $memcached = new Memcached();
        $memcached->addServer('localhost', 11211);
        $memcached->flush();
        $memcachedCache = new MemcachedCache($memcached);
        $this->cache = new GeekCache\Cache\StageableCache($memcachedCache);
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
    
    public function testGetCount()
    {
        $getCount = $this->cache->getGetCount();
        $this->cache->get(self::KEY);
        $this->assertEquals($getCount + 1, $this->cache->getGetCount());
    }
    
    public function testStageGetCount()
    {
        $getCount = $this->cache->getGetCount();
        $this->cache->stage(self::KEY);
        $this->cache->get(self::KEY2);
        // because of the staging, it should do only one request to get both results
        $this->cache->get(self::KEY);
        $this->assertEquals($getCount + 1, $this->cache->getGetCount());
        $this->assertStageEmpty();
    }
    
    public function testStageGets()
    {
        $this->cache->put(self::KEY, self::VALUE, 1);
        $this->cache->put(self::KEY2, self::VALUE2, 1);
        $this->cache->stage(self::KEY);
        $this->assertEquals(self::VALUE2, $this->cache->get(self::KEY2));
        $this->assertEquals(self::VALUE, $this->cache->get(self::KEY));
        $this->assertStageEmpty();
    }
    
    public function testGetAfterStageOnlyGetsFromPendingOnce()
    {
        $getCount = $this->cache->getGetCount();
        $this->cache->put(self::KEY, self::VALUE, 1);
        $this->cache->put(self::KEY2, self::VALUE2, 1);
        $this->cache->stage(self::KEY);
        $this->assertEquals(self::VALUE2, $this->cache->get(self::KEY2));
        $this->assertEquals(self::VALUE, $this->cache->get(self::KEY));
        // we staged only once, but get twice; the second "get" should get from cache
        $this->assertEquals(self::VALUE, $this->cache->get(self::KEY));
        $this->assertEquals($getCount + 2, $this->cache->getGetCount());
        $this->assertStageEmpty();
    }
    
    public function testGetAfterStageTwiceGetsFromPendingTwice()
    {
        $getCount = $this->cache->getGetCount();
        $this->cache->put(self::KEY, self::VALUE, 1);
        $this->cache->put(self::KEY2, self::VALUE2, 1);
        $this->cache->stage(self::KEY);
        $this->cache->stage(self::KEY);
        $this->assertEquals(self::VALUE2, $this->cache->get(self::KEY2));
        $this->assertEquals(self::VALUE, $this->cache->get(self::KEY));
        $this->assertEquals(self::VALUE, $this->cache->get(self::KEY));
        $this->assertEquals($getCount + 1, $this->cache->getGetCount());
        $this->assertStageEmpty();
    }

    public function testStagedItemsAreGottenOnlyOnce()
    {
        $getCount = $this->cache->getGetCount();
        $this->cache->put(self::KEY, self::VALUE, 1);
        $this->cache->put(self::KEY2, self::VALUE2, 1);
        $this->cache->stage(self::KEY);
        // count + 1
        $this->assertEquals(self::VALUE2, $this->cache->get(self::KEY2));
        $this->assertEquals(self::VALUE, $this->cache->get(self::KEY));
        // count + 1
        $this->assertEquals(self::VALUE2, $this->cache->get(self::KEY2));
        // we staged only once, then got key 2 twice. But the second time we get
        // key 1, we should have to go to cache, because it shouldn't get from staged
        // a second time
        // count + 1
        $this->assertEquals(self::VALUE, $this->cache->get(self::KEY));
        $this->assertEquals($getCount + 3, $this->cache->getGetCount());
        $this->assertStageEmpty();
    }
    
    public function testStageGetsFromUnsetCaches()
    {
        $getCount = $this->cache->getGetCount();
        $this->cache->stage(self::KEY);
        $this->assertEquals(false, $this->cache->get(self::KEY2));
        $this->assertEquals(false, $this->cache->get(self::KEY));
        $this->assertEquals($getCount + 1, $this->cache->getGetCount());
        $this->assertStageEmpty();
    }
    
    public function testGetDoublyStagedKeyDirectly()
    {
        $getCount = $this->cache->getGetCount();
        $this->cache->put(self::KEY, self::VALUE, 1);
        $this->cache->put(self::KEY2, self::VALUE2, 1);
        $this->cache->stage(self::KEY);
        $this->cache->stage(self::KEY);
        $this->assertEquals(self::VALUE, $this->cache->get(self::KEY));
        $this->assertEquals(self::VALUE, $this->cache->get(self::KEY));
        $this->assertEquals($getCount + 1, $this->cache->getGetCount());
        $this->assertStageEmpty();
    }
    
    public function testGetDoublyStagedKeyDirectlyThreeTimes()
    {
        $getCount = $this->cache->getGetCount();
        $this->cache->put(self::KEY, self::VALUE, 1);
        $this->cache->put(self::KEY2, self::VALUE2, 1);
        $this->cache->stage(self::KEY);
        $this->cache->stage(self::KEY);
        $this->assertEquals(self::VALUE, $this->cache->get(self::KEY));
        $this->assertEquals(self::VALUE, $this->cache->get(self::KEY));
        $this->assertEquals(self::VALUE, $this->cache->get(self::KEY));
        $this->assertEquals($getCount + 2, $this->cache->getGetCount());
        $this->assertStageEmpty();
    }
    
    public function testStageAfterRead()
    {
        $getCount = $this->cache->getGetCount();
        $this->cache->put(self::KEY, self::VALUE, 1);
        $this->cache->put(self::KEY2, self::VALUE2, 1);
        $this->cache->stage(self::KEY);
        $this->cache->stage(self::KEY);
        $this->cache->stage(self::KEY2);
        $this->assertEquals(self::VALUE, $this->cache->get(self::KEY));
        $this->cache->stage(self::KEY);
        $this->assertEquals(self::VALUE, $this->cache->get(self::KEY));
        $this->assertEquals(self::VALUE, $this->cache->get(self::KEY));
        $this->assertEquals(self::VALUE2, $this->cache->get(self::KEY2));
        $this->assertEquals($getCount + 1, $this->cache->getGetCount());
        $this->assertStageEmpty();
    }
    
    public function testCacheItemStaging()
    {
        $cacheItem1 = new NormalCacheItem($this->cache, self::KEY);
        $cacheItem2 = new NormalCacheItem($this->cache, self::KEY2);
        $cacheItem1->put(self::VALUE);
        $cacheItem2->put(self::VALUE2);
        $getCount = $this->cache->getGetCount();
        $cacheItem1->stage();
        $this->assertEquals(self::VALUE2, $cacheItem2->get());
        $this->assertEquals(self::VALUE, $cacheItem1->get());
        $this->assertEquals($getCount + 1, $this->cache->getGetCount());
        $this->assertStageEmpty();
    }
    
    public function assertStageEmpty()
    {
        $this->assertEquals(0, $this->cache->getStagedRequestsCount());
        $this->assertEquals(0, $this->cache->getStagedResultsCount());
    }
}
