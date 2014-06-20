<?php
use Mockery as m;
class TaggedCacheTest extends BaseCacheTest
{
	private $arraycache;

	public function setUp()
	{
		parent::setUp();
		$this->parentcache = new GeekCache\Cache\ArrayCache;
		$this->tagset = m::mock( 'GeekCache\\Cache\\TagSet' );
		$this->tagset->shouldReceive( 'getSignature' )
			->andReturn( 'foo' )
			->byDefault();
		$policy = new GeekCache\Cache\TaggedFreshnessPolicy( $this->tagset );
		$this->cache = new GeekCache\Cache\SoftInvalidatableCache( $this->parentcache, $policy );
	}

	public function testCacheInvalidatesWhenHashChanges()
	{
		$this->cache->put( static::KEY, static::VALUE );
		$this->tagset->shouldReceive( 'getSignature' )
			->andReturn( 'bar' );
		
		$this->assertFalse( $this->cache->get( static::KEY ) );
	}

	public function testGetStaleWhenHashChanges()
	{
		$this->cache->put( static::KEY, static::VALUE );
		$this->tagset->shouldReceive( 'getSignature' )
			->andReturn( 'bar' );

		$this->assertEquals( static::VALUE, $this->cache->getStale( static::KEY ) );
	}

	public function testGetStaleFromWrappedSoftInvalidatable()
	{
		$policy = new GeekCache\Cache\GracePeriodFreshnessPolicy();
		$cache = new GeekCache\Cache\SoftInvalidatableCache( $this->cache, $policy, $this->cache );

		$this->cache->put( static::KEY, static::VALUE, 1 );
		$this->tagset->shouldReceive( 'getSignature' )
			->andReturn( 'bar' );

		$value      = $this->cache->get( static::KEY );
		$stalevalue = $this->cache->getStale( static::KEY );

		$this->assertFalse( $value );
		$this->AssertEquals( static::VALUE, $stalevalue );
	}

	public function testGetStaleFromWrappedSoftInvalidatableReverse()
	{
		$policy = new GeekCache\Cache\GracePeriodFreshnessPolicy();
		$this->cache = new GeekCache\Cache\SoftInvalidatableCache( $this->parentcache, $policy  );
		$taggedPolicy = new GeekCache\Cache\TaggedFreshnessPolicy( $this->tagset );
		$this->cache = new GeekCache\Cache\SoftInvalidatableCache( $this->cache, $taggedPolicy, $this->cache );
		$this->cache->put( static::KEY, static::VALUE, 0.01 );
		$this->assertEquals( static::VALUE, $this->cache->get( static::KEY ) );
		usleep( 11000 );
		
		$value      = $this->cache->get( static::KEY );
		$stalevalue = $this->cache->getStale( static::KEY );

		$this->assertFalse( $value );
		$this->AssertEquals( static::VALUE, $stalevalue );
	}
}
