<?php

use GeekCache\Cache\MemcachedCache;
use GeekCache\Cache\SoftInvalidatableCache;
use GeekCache\Cache\TaggedFreshnessPolicy;

class TaggedCacheMemcachedTest extends BaseCacheTestAbstract
{
    
    const TAG_NAMES = ['TAG_1', 'TAG_2'];
    const TAG_NAMES2 = ['TAG_3', 'TAG_4'];
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
        $getCount = $this->parentcache->getGetCount();
        $this->cache->get(self::KEY);
        $this->assertEquals($getCount + 1, $this->parentcache->getGetCount());
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
