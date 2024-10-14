<?php
use Mockery as m;

class TaggedCacheTest extends BaseCacheTest
{
    private $parentcache;
    private $tagset;
    
    public function setUp(): void
    {
        parent::setUp();
        $this->parentcache = new GeekCache\Cache\ArrayCache;
        $this->tagset = m::mock('GeekCache\\Tag\\TagSet');
        $this->tagset->shouldReceive('getSignature')
            ->andReturn('foo')
            ->byDefault();
        $this->tagset->shouldReceive('stage');
        $policy = new GeekCache\Cache\TaggedFreshnessPolicy($this->tagset);
        $this->cache = new GeekCache\Cache\SoftInvalidatableCache($this->parentcache, $policy);
    }

    public function tearDown(): void
    {
        m::close();
        parent::tearDown();
    }
    public function testCachePassesTrueIntoRegenerator()
    {
        $this->cache->put(static::KEY, static::VALUE);
        $this->tagset->shouldReceive('getSignature')
            ->andReturn('bar');

        $regenerator = m::mock('stdClass');
        $regenerator->shouldReceive('regenerate')
            ->with(true)
            ->once()
            ->andReturn(static::VALUE2);

        $this->cache->get(static::KEY, [$regenerator, 'regenerate']);
    }

    public function testCacheInvalidatesWhenHashChanges()
    {
        $this->cache->put(static::KEY, static::VALUE);
        $this->tagset->shouldReceive('getSignature')
            ->andReturn('bar');

        $this->assertFalse($this->cache->get(static::KEY));
    }

    public function testSameLookupWithoutTagsDoesNotCollide()
    {
        $this->cache->put(static::KEY, static::VALUE);
        $this->assertFalse($this->parentcache->get(static::KEY));
    }

    public function testGetStaleWhenHashChanges()
    {
        $this->cache->put(static::KEY, static::VALUE);
        $this->tagset->shouldReceive('getSignature')
            ->andReturn('bar');

        $regenerator = function () {
            return false;
        };

        $this->assertEquals(static::VALUE, $this->cache->get(static::KEY, $regenerator));
    }

    public function testRegeneratesWhenHashChanges()
    {
        $this->cache->put(static::KEY, static::VALUE);
        $this->tagset->shouldReceive('getSignature')
            ->andReturn('bar');


        $value2 = self::VALUE2;
        $regenerator = function () use ($value2) {
            return $value2;
        };

        $this->assertEquals(static::VALUE2, $this->cache->get(static::KEY, $regenerator));
    }

    public function testRegeneratesNullWhenHashChanges()
    {
        $this->cache->put(static::KEY, static::VALUE);
        $this->tagset->shouldReceive('getSignature')
            ->andReturn('bar');


        $value2 = self::VALUE2;
        $regenerator = function () use ($value2) {
            return null;
        };

        $this->assertNull($this->cache->get(static::KEY, $regenerator));
    }

    public function testGetStaleFromWrappedSoftInvalidatable()
    {
        $policy = new GeekCache\Cache\GracePeriodFreshnessPolicy();
        $this->cache = new GeekCache\Cache\SoftInvalidatableCache($this->cache, $policy, $this->cache);

        $this->cache->put(static::KEY, static::VALUE, 1);
        $this->tagset->shouldReceive('getSignature')
            ->andReturn('bar');

        $value      = $this->cache->get(static::KEY);
        $regenerator = function () {
            return false;
        };
        $stalevalue = $this->cache->get(static::KEY, $regenerator);

        $this->assertFalse($value);
        $this->AssertEquals(static::VALUE, $stalevalue);
    }

    public function testGetStaleFromWrappedSoftInvalidatableReverse()
    {
        $policy = new GeekCache\Cache\GracePeriodFreshnessPolicy();
        $this->cache = new GeekCache\Cache\SoftInvalidatableCache($this->parentcache, $policy);
        $taggedPolicy = new GeekCache\Cache\TaggedFreshnessPolicy($this->tagset);
        $this->cache = new GeekCache\Cache\SoftInvalidatableCache($this->cache, $taggedPolicy, $this->cache);
        $this->cache->put(static::KEY, static::VALUE, 0.01);
        $this->assertEquals(static::VALUE, $this->cache->get(static::KEY));
        usleep(11000);

        $value      = $this->cache->get(static::KEY);
        $regenerator = function () {
            return false;
        };
        $stalevalue = $this->cache->get(static::KEY, $regenerator);

        $this->assertFalse($value);
        $this->AssertEquals(static::VALUE, $stalevalue);
    }

    public function testGetStaleFromWrappedSoftInvalidatableReverseRegenerates()
    {
        $policy = new GeekCache\Cache\GracePeriodFreshnessPolicy();
        $this->cache = new GeekCache\Cache\SoftInvalidatableCache($this->parentcache, $policy);
        $taggedPolicy = new GeekCache\Cache\TaggedFreshnessPolicy($this->tagset);
        $this->cache = new GeekCache\Cache\SoftInvalidatableCache($this->cache, $taggedPolicy, $this->cache);
        $this->cache->put(static::KEY, static::VALUE, 0.01);
        $this->assertEquals(static::VALUE, $this->cache->get(static::KEY));
        usleep(11000);

        $value      = $this->cache->get(static::KEY);
        $value2 = static::VALUE2;
        $regenerator = function () use ($value2) {
            return $value2;
        };

        $regeneratedValue = $this->cache->get(static::KEY, $regenerator, 0.01);

        $newvalue = $this->cache->get(static::KEY);
        usleep(11000);
        $expiredvalue = $this->cache->get(static::KEY);


        $this->assertFalse($value);
        $this->AssertEquals(static::VALUE2, $regeneratedValue);
        $this->AssertEquals(static::VALUE2, $newvalue);
        $this->AssertFalse($expiredvalue);
    }
}
