<?php
class SoftExpiringTest extends BaseCacheTest
{
	private $arraycache;
	const HARDTTL = 10;

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
		$this->cache = new Geek\Cache\SoftExpiringCache( $this->parentcache, 1 );
		$this->cache->put( self::KEY, self::VALUE );
		$this->assertEquals( self::VALUE, $this->cache->get( self::KEY ) );
		usleep( 1100000 );
		$this->assertFalse( $this->cache->get( self::KEY ) );
	}

	public function testTtl()
	{
		$this->cache = new Geek\Cache\SoftExpiringCache( $this->parentcache, 0.01 );
		$this->cache->put( self::KEY, self::VALUE );
		$this->assertEquals( self::VALUE, $this->cache->get( self::KEY ) );
		usleep( 11000 );
		$this->assertFalse( $this->cache->get( self::KEY ) );
	}

	public function testTtlNegative()
	{
		$this->cache = new Geek\Cache\SoftExpiringCache( $this->parentcache, -1 );
		$this->cache->put( self::KEY, self::VALUE );
		$this->assertFalse( $this->cache->get( self::KEY ) );
	}

	public function testSoftExpriration()
	{
		$this->cache = new Geek\Cache\SoftExpiringCache( $this->parentcache, -1 );
		$this->cache->put( self::KEY, self::VALUE );
		$this->assertEquals( self::VALUE, $this->cache->getStale( self::KEY ) );
	}

	public function testPassesHardTtlToParent()
	{
		$parentcache = new ArrayCacheTtlSpy();
		$this->cache = new Geek\Cache\SoftExpiringCache( $parentcache, 1 );
		$this->cache->put( self::KEY, self::VALUE, self::HARDTTL );

		$this->assertEquals( self::HARDTTL, $parentcache->ttl );
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

