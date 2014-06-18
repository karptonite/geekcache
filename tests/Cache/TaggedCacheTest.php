<?php
use Mockery as m;
class TaggedCacheTest extends BaseCacheTest
{
	private $arraycache;

	public function setUp()
	{
		parent::setUp();
		$this->parentcache = new Geek\Cache\ArrayCache;
		$this->tagset = m::mock( 'Geek\\Cache\\TagSet' );
		$this->tagset->shouldReceive( 'getSignature' )
			->andReturn( 'foo' )
			->byDefault();
		$taggedInvalidator = new Geek\Cache\TaggedValidator( $this->tagset );
		$this->cache = new Geek\Cache\SoftInvalidatableCache( $this->parentcache, $taggedInvalidator );
	}

	public function tearDown()
	{
		m::close();
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
		$validator = new Geek\Cache\GracePeriodValidator();
		$cache = new Geek\Cache\SoftInvalidatableCache( $this->cache, $validator, $this->cache );

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
		$validator = new Geek\Cache\GracePeriodValidator();
		$this->cache = new Geek\Cache\SoftInvalidatableCache( $this->parentcache, $validator  );
		$taggedValidator = new Geek\Cache\TaggedValidator( $this->tagset );
		$this->cache = new Geek\Cache\SoftInvalidatableCache( $this->cache, $taggedValidator, $this->cache );
		$this->cache->put( static::KEY, static::VALUE, 0.01 );
		$this->assertEquals( static::VALUE, $this->cache->get( static::KEY ) );
		usleep( 11000 );
		
		$value      = $this->cache->get( static::KEY );
		$stalevalue = $this->cache->getStale( static::KEY );

		$this->assertFalse( $value );
		$this->AssertEquals( static::VALUE, $stalevalue );
	}
}
