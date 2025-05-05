<?php

use GeekCache\Cache\KeyReviser;
use GeekCache\Cache\MemcachedCache;
use GeekCache\Cache\SoftInvalidatableCache;
use GeekCache\Cache\TaggedFreshnessPolicy;

class TaggedCacheMemcachedTest extends BaseCacheTestAbstract
{
    
    const TAG_NAMES = ['TAG_1', 'TAG_2'];
    const TAG_NAMES2 = ['TAG_3', 'TAG_2'];
    
    const TAG_NAMES3 = ['TAG_4'];
    private $parentcache;
    private $factory;

    public function setUp(): void
    {
        parent::setUp();
        $memcached = new Memcached();
        $memcached->addServer('localhost', 11211);
        $memcached->flush();
        $memcachedCache = new MemcachedCache($memcached);
        $this->parentcache = new GeekCache\Cache\StageableCache($memcachedCache);
        $tagFactory = new GeekCache\Tag\TagFactory($this->parentcache);
        $this->factory    = new GeekCache\Tag\TagSetFactory($tagFactory);
        $tagSet = $this->factory->makeTagSet(self::TAG_NAMES);
        $policy = new TaggedFreshnessPolicy($tagSet);
        $this->cache =  new SoftInvalidatableCache($this->parentcache, $policy);
    }
    
    public function testTaggedCacheInvalidates()
    {
        $tagSet = $this->factory->makeTagSet(self::TAG_NAMES);
        $policy = new TaggedFreshnessPolicy($tagSet);
        $cache =  new SoftInvalidatableCache($this->parentcache, $policy);
        $cache->put(self::KEY, self::VALUE);
        $this->assertStageEmpty();
        $this->assertEquals(self::VALUE, $cache->get(self::KEY));
        $this->assertStageEmpty();
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
        $getCount = $this->parentcache->getGetCount();
        $this->cache->get(self::KEY);
        $this->assertEquals($getCount + 1, $this->parentcache->getGetCount());
        $this->assertStageEmpty();
    }
    
    public function testTaggedCacheLookupHitsCacheOnceWithOneKey()
    {
        // this will pull both tags before getting them
        $tagSet = $this->factory->makeTagSet(self::TAG_NAMES3);
        $policy = new TaggedFreshnessPolicy($tagSet);
        $firstCache =  new SoftInvalidatableCache($this->parentcache, $policy);
        $firstCache->put(self::KEY, self::VALUE);
        
        $getCount = $this->parentcache->getGetCount();
        
        $tagSet = $this->factory->makeTagSet(self::TAG_NAMES3);
        $policy = new TaggedFreshnessPolicy($tagSet);
        $secondCache =  new SoftInvalidatableCache($this->parentcache, $policy);
        $secondCache->get(self::KEY);
        $this->assertEquals($getCount + 1, $this->parentcache->getGetCount());
        $this->assertStageEmpty();
    }

    public function testTaggedCacheLookupHitsCacheOnceReverseReadPut()
    {
        // this will pull both tags before getting them
        $this->cache->put(self::KEY, self::VALUE);
        
        $getCount = $this->parentcache->getGetCount();
        $tagSet = $this->factory->makeTagSet(self::TAG_NAMES);
        $policy = new TaggedFreshnessPolicy($tagSet);
        $otherCache =  new SoftInvalidatableCache($this->parentcache, $policy);
        $otherCache->get(self::KEY);
        $this->assertEquals($getCount + 1, $this->parentcache->getGetCount());
        $otherCache->put(self::KEY, self::VALUE);
        $this->assertEquals($getCount + 2, $this->parentcache->getGetCount());
        $this->assertStageEmpty();
    }


    public function testMultipleTaggedCacheLookupHitsCacheOnce()
    {
        // this will pull both tags before getting them
        $this->cache->put(self::KEY, self::VALUE);
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

    public function testPurgingForOneTagsetDoesntCauseASecondToFailForStagingWhenBothAreStagedNoMemo()
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


    public function testOnlyCreatesTagCacheWhenNecessary()
    {
        $tagKey = "tag_" . self::TAG_NAMES[0];
        $this->assertFalse($this->parentcache->get($tagKey));
        $this->cache->put(self::KEY, self::VALUE);
        $this->parentcache->delete($tagKey);
        $this->assertFalse($this->parentcache->get($tagKey));
        $result = $this->cache->get(self::KEY);
        $this->assertFalse($result);
        $this->assertFalse($this->parentcache->get($tagKey));
    }


    public function assertStageEmpty()
    {
        $this->assertEquals(0, $this->parentcache->getStagedRequestsCount());
        $this->assertEquals(0, $this->parentcache->getStagedResultsCount());
    }
    public function dumpCounts()
    {
        echo "\nrequest count: " . $this->parentcache->getStagedRequestsCount() . "\n";
        echo "results count: " . $this->parentcache->getStagedResultsCount() . "\n";
    }

}
