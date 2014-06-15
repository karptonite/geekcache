<?php
class SoftExpiringTest extends BaseCacheTest
{
	private $arraycache;

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
	
}

