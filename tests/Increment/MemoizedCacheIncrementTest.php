<?php
class MemoizedCacheIncrementTest extends BaseIncrementableCacheTest
{
	private $primarycache;

	public function setUp()
	{
		parent::setUp();
		$this->primarycache = new Geek\Cache\ArrayCache;
		$this->memoizedcache = new Geek\Cache\ArrayCache;
		$this->cache = new Geek\Cache\MemoizedCache( $this->primarycache, $this->memoizedcache );
	}

	public function testMemoizedCacheIncrementsThePrimary()
	{
		$this->cache->put( self::KEY, 3 );
		$this->cache->increment( self::KEY, 2 );
		$this->assertEquals( 5, $this->primarycache->get( self::KEY ) );
	}

	public function testMemoizedCacheIncrementsWhenItHasNotRead()
	{
		$this->primarycache->put( self::KEY, 3 );
		$this->cache->increment( self::KEY, 2 );
		$this->assertEquals( 5, $this->cache->get( self::KEY ) );
	}
}

