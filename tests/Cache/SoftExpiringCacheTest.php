<?php
class SoftExpiringTest extends BaseCacheTest
{
	private $arraycache;
	const GRACEPERIOD = 10;

	public function setUp()
	{
		parent::setUp();
		$this->parentcache = new GeekCache\Cache\ArrayCache;
		$policy = new GeekCache\Cache\GracePeriodFreshnessPolicy;
		$this->cache = new GeekCache\Cache\SoftInvalidatableCache( $this->parentcache, $policy );
	}
	
	/**
	 * @group slowTests
	 */
	public function testTtlInteger()
	{
		$this->cache->put( self::KEY, self::VALUE, 1 );
		$this->assertEquals( self::VALUE, $this->cache->get( self::KEY ) );
		usleep( 1100000 );
		$this->assertFalse( $this->cache->get( self::KEY ) );
	}

	public function testTtl()
	{
		$this->cache->put( self::KEY, self::VALUE, 0.01 );
		$this->assertEquals( self::VALUE, $this->cache->get( self::KEY ) );
		usleep( 11000 );
		$this->assertFalse( $this->cache->get( self::KEY ) );
	}

	public function testTtlNegative()
	{
		$this->cache->put( self::KEY, self::VALUE, -1 );
		$this->assertFalse( $this->cache->get( self::KEY ) );
	}

	public function testSoftExpriration()
	{
		$this->cache->put( self::KEY, self::VALUE, -1 );
		$this->assertEquals( self::VALUE, $this->cache->getStale( self::KEY ) );
	}

	public function testPassesHardTtlToParent()
	{
		$parentcache = new ArrayCacheTtlSpy();
		$policy = new GeekCache\Cache\GracePeriodFreshnessPolicy( self::GRACEPERIOD );
		$this->cache = new GeekCache\Cache\SoftInvalidatableCache( $parentcache, $policy );
		$this->cache->put( self::KEY, self::VALUE, 1 );

		$this->assertEquals( self::GRACEPERIOD + 1, $parentcache->ttl );
	}
}

class ArrayCacheTtlSpy extends GeekCache\Cache\ArrayCache
{
	public $ttl;

	public function put( $key, $value, $ttl = null )
	{
		$this->ttl = $ttl;
		parent::put( $key, $value, $ttl );
	}
	

}

