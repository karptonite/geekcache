<?php
class MemcacheCacheLiveTest extends BaseCacheTest
{

	public function setUp()
	{
		parent::setUp();
		$memcache = new Memcache();
		$memcache->connect('localhost', 11211);
		$memcache->flush();
		$this->cache = new GeekCache\Cache\MemcacheCache( $memcache );
	}

	/**
	 * @group slowTests
	 */
	public function testTtlInteger()
	{
		$this->cache->put( self::KEY, self::VALUE, 1 );
		$this->assertEquals( self::VALUE, $this->cache->get( self::KEY ) );
		usleep( 2100000 );
		$this->assertFalse( $this->cache->get( self::KEY ) );
	}
}
