<?php

use GeekCache\Cache\MemcachedCache;
use GeekCache\Cache\SoftInvalidatableCache;
use GeekCache\Cache\TaggedFreshnessPolicy;

class TaggedCacheWithMemoMemcachedTest extends BaseCacheTestAbstract
{

    const TAG_NAMES = ['TAG_1', 'TAG_2'];
    const TAG_NAMES2 = ['TAG_3', 'TAG_2'];

    private $secondaryCache;
    private $parentcache;
    private $memoizedcache;
    private $factory;

    public function setUp(): void
    {
        parent::setUp();
        $this->prepareCache();
    }
    
    protected function prepareCache()
    {
        $memcached = new Memcached();
        $memcached->addServer('localhost', 11211);
        $memcached->flush();
        $memcachedCache = new MemcachedCache($memcached);
        $this->secondaryCache = new GeekCache\Cache\ArrayCache;
        $this->parentcache = new GeekCache\Cache\StageableCache($memcachedCache);
        $this->memoizedcache = new GeekCache\Cache\MemoizedCache($this->parentcache, $this->secondaryCache);
        $tagFactory = new GeekCache\Tag\TagFactory($this->memoizedcache);
        $this->factory    = new GeekCache\Tag\TagSetFactory($tagFactory);
        $tagSet = $this->factory->makeTagSet(self::TAG_NAMES);
        $policy = new TaggedFreshnessPolicy($tagSet);
        $this->cache =  new SoftInvalidatableCache($this->parentcache, $policy);
    }
    
    protected function getCache()
    {
        $tagSet = $this->factory->makeTagSet(self::TAG_NAMES);
        $policy = new TaggedFreshnessPolicy($tagSet);
        return  new SoftInvalidatableCache($this->parentcache, $policy);
    }
    
    public function testMemoizationDoesNotStageWhenValueIsAvailable()
    {
        // this should add the keys to the static cache;
        $this->cache->put(self::KEY, self::VALUE);
        $this->secondaryCache->clear();
        $this->cache->get(self::KEY);
        // this should get the item from cache
        $this->cache->get(self::KEY);
        $this->assertStageEmpty();
    }

    public function testMemoizationHandlesStagedItemCorrectly()
    {
        $this->getCache()->put(self::KEY, self::VALUE);
        $this->secondaryCache->clear();
        $this->assertStageEmpty();

        //        echo "\n\nSTAGE IS EMPTY\n\n";
        
        $this->getCache()->stage(self::KEY);
        $this->getCache()->get(self::KEY);
        $this->assertStageEmpty();
    }
    
    public function testMemoizationHandlesMultipleStagedItemsCorrectly()
    {
        $this->getCache()->put(self::KEY, self::VALUE);
        $this->secondaryCache->clear();
        $this->assertStageEmpty();
        
        $this->getCache()->stage(self::KEY);
        $this->getCache()->stage(self::KEY);
        $this->getCache()->stage(self::KEY);
        
        $this->getCache()->get(self::KEY);
        // this should get the item from cache
        $this->getCache()->get(self::KEY);
        $this->getCache()->get(self::KEY);
        $this->assertStageEmpty();
    }
    
    public function dumpCounts()
    {
        echo "request count: " . $this->parentcache->getStagedRequestsCount() . "\n";
        echo "results count: " . $this->parentcache->getStagedResultsCount() . "\n";
    }


    public function testTaggedCacheInvalidates()
    {
        $tagSet = $this->factory->makeTagSet(self::TAG_NAMES);
        $policy = new TaggedFreshnessPolicy($tagSet);
        $cache =  new SoftInvalidatableCache($this->parentcache, $policy);
        $cache->put(self::KEY, self::VALUE);
        $this->assertEquals(self::VALUE, $cache->get(self::KEY));
        $tagSet->clearAll();
        $this->assertFalse($cache->get(self::KEY));
        $this->assertStageEmpty();
    }

    public function testTaggedCacheLookupMiss()
    {
        $getCount = $this->parentcache->getGetCount();
        $this->cache->get(self::KEY);
        $this->assertEquals($getCount + 1, $this->parentcache->getGetCount());
        $this->assertStageEmpty();
    }


    public function testTaggedCacheLookupHitsCacheOnce()
    {
        // this will pull both tags before getting them
        $this->cache->put(self::KEY, self::VALUE);
        $this->secondaryCache->clear();
        $getCount = $this->parentcache->getGetCount();
        $this->cache->get(self::KEY);
        $this->assertEquals($getCount + 1, $this->parentcache->getGetCount());
        $this->assertStageEmpty();
    }

    public function testMultipleTaggedCacheLookupHitsCacheOnce()
    {
        // this will pull both tags before getting them
        $this->cache->put(self::KEY, self::VALUE);
        $this->secondaryCache->clear();
        $tagSet = $this->factory->makeTagSet(self::TAG_NAMES);
        $policy = new TaggedFreshnessPolicy($tagSet);
        $otherCache =  new SoftInvalidatableCache($this->parentcache, $policy);
        $otherCache->put(self::KEY2, self::VALUE2);
        $this->assertStageEmpty();
        $getCount = $this->parentcache->getGetCount();
        $this->cache->stage(self::KEY);
        $otherCache->get(self::KEY2);
        $this->cache->get(self::KEY);
        $this->assertStageEmpty();
        $this->assertEquals($getCount + 1, $this->parentcache->getGetCount());
    }

    public function testMultipleTaggedCacheLookupHitsCacheOnceDifferentTags()
    {
        // this will pull both tags before getting them
        $this->cache->put(self::KEY, self::VALUE);
        $this->secondaryCache->clear();
        $tagSet = $this->factory->makeTagSet(self::TAG_NAMES2);
        $policy = new TaggedFreshnessPolicy($tagSet);
        $otherCache =  new SoftInvalidatableCache($this->parentcache, $policy);
        $otherCache->put(self::KEY2, self::VALUE2);
        $this->assertStageEmpty();
        $getCount = $this->parentcache->getGetCount();
        $this->cache->stage(self::KEY);
        $otherCache->get(self::KEY2);
        $this->cache->get(self::KEY);
        $this->assertStageEmpty();
        $this->assertEquals($getCount + 1, $this->parentcache->getGetCount());
    }

    public function testPurgingForOneTagsetDoesntCauseASecondToFailForStaging()
    {
        $tagSet = $this->factory->makeTagSet(self::TAG_NAMES2);
        $policy = new TaggedFreshnessPolicy($tagSet);
        $otherCache =  new SoftInvalidatableCache($this->parentcache, $policy);

        $this->cache->put(self::KEY, self::VALUE);
        $this->assertStageEmpty();
        $getCount = $this->parentcache->getGetCount();
        $this->cache->stage(self::KEY);
        $otherCache->get(self::KEY2);
        $this->cache->get(self::KEY);
        $this->assertStageEmpty();
        $this->assertEquals($getCount + 1, $this->parentcache->getGetCount());
    }

    public function testPurgingForOneTagsetDoesntCauseASecondToFailForStagingWhenBothAreStaged()
    {
        $tagSet = $this->factory->makeTagSet(self::TAG_NAMES2);
        $policy = new TaggedFreshnessPolicy($tagSet);
        $otherCache =  new SoftInvalidatableCache($this->parentcache, $policy);

        $this->cache->put(self::KEY, self::VALUE);
        $this->assertStageEmpty();
        $getCount = $this->parentcache->getGetCount();
        $this->cache->stage(self::KEY);
        $otherCache->stage(self::KEY2);
        $otherCache->get(self::KEY2);
        $this->cache->get(self::KEY);
        $this->assertStageEmpty();
        $this->assertEquals($getCount + 1, $this->parentcache->getGetCount());
    }

    public function testGettingTagSignatureUsesOnlyOneGet()
    {
        $getCount = $this->parentcache->getGetCount();
        $this->cache->put(self::KEY, self::VALUE);
        $this->assertStageEmpty();
        $this->assertEquals($getCount + 1, $this->parentcache->getGetCount());
    }


    public function assertStageEmpty()
    {
        $this->assertEquals(0, $this->parentcache->getStagedRequestsCount());
        $this->assertEquals(0, $this->parentcache->getStagedResultsCount());
    }
}
