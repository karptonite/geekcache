<?php
class SoftExpiringTest extends BaseCacheTest
{
	private $arraycache;
	const GRACEPERIOD = 10;

	public function setUp()
	{
		parent::setUp();
		$this->parentcache = new Geek\Cache\ArrayCache;
		$gracePeriodValidator = new Geek\Cache\GracePeriodValidator;
		$this->cache = new Geek\Cache\SoftInvalidatableCache( $this->parentcache, $gracePeriodValidator );
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
		$gracePeriodValidator = new Geek\Cache\GracePeriodValidator( self::GRACEPERIOD );
		$this->cache = new Geek\Cache\SoftInvalidatableCache( $parentcache, $gracePeriodValidator );
		$this->cache->put( self::KEY, self::VALUE, 1 );

		$this->assertEquals( self::GRACEPERIOD + 1, $parentcache->ttl );
	}
}

class ArrayCacheTtlSpy extends Geek\Cache\ArrayCache
{
	public $ttl;

	public function put( $key, $value, $ttl = null )
	{
		$this->ttl = $ttl;
		parent::put( $key, $value, $ttl );
	}
	

}

