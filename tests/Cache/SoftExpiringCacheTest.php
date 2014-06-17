<?php
class SoftExpiringTest extends BaseCacheTest
{
	private $arraycache;
	const GRACEPERIOD = 10;

	public function setUp()
	{
		parent::setUp();
		$this->parentcache = new Geek\Cache\ArrayCache;
		$this->cache = new Geek\Cache\SoftExpiringCache( $this->parentcache );
	}
	
	/**
	 * @group slowTests
	 */
	public function testTtlInteger()
	{
		$this->cache = new Geek\Cache\SoftExpiringCache( $this->parentcache );
		$this->cache->put( self::KEY, self::VALUE, 1 );
		$this->assertEquals( self::VALUE, $this->cache->get( self::KEY ) );
		usleep( 1100000 );
		$this->assertFalse( $this->cache->get( self::KEY ) );
	}

	public function testTtl()
	{
		$this->cache = new Geek\Cache\SoftExpiringCache( $this->parentcache  );
		$this->cache->put( self::KEY, self::VALUE, 0.01 );
		$this->assertEquals( self::VALUE, $this->cache->get( self::KEY ) );
		usleep( 11000 );
		$this->assertFalse( $this->cache->get( self::KEY ) );
	}

	public function testTtlNegative()
	{
		$this->cache = new Geek\Cache\SoftExpiringCache( $this->parentcache );
		$this->cache->put( self::KEY, self::VALUE, -1 );
		$this->assertFalse( $this->cache->get( self::KEY ) );
	}

	public function testSoftExpriration()
	{
		$this->cache = new Geek\Cache\SoftExpiringCache( $this->parentcache, 0 );
		$this->cache->put( self::KEY, self::VALUE, -1 );
		$this->assertEquals( self::VALUE, $this->cache->getStale( self::KEY ) );
	}

	public function testPassesHardTtlToParent()
	{
		$parentcache = new ArrayCacheTtlSpy();
		$this->cache = new Geek\Cache\SoftExpiringCache( $parentcache, self::GRACEPERIOD );
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

