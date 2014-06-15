<?php
class SerializedCacheTest extends BaseCacheTest
{
	private $parentcache;

	public function setUp()
	{
		parent::setUp();
		$this->parentcache = new Geek\Cache\ArrayCache;
		$this->cache = new Geek\Cache\SerializedCache( $this->parentcache );
	}
	
	public function testCacheSerializes()
	{
		$this->cache->put( self::KEY, self::VALUE );
		$this->assertEquals( serialize( self::VALUE ), $this->parentcache->get( self::KEY ) );
	}
}
