<?php
class MemoizedCacheTest extends BaseCacheTest
{
	private $parentcache;

	public function setUp()
	{
		parent::setUp();
		$this->primarycache = new Geek\Cache\ArrayCache;
		$this->memoizedcache = new Geek\Cache\ArrayCache;
		$this->cache = new Geek\Cache\MemoizedCache( $this->primarycache, $this->memoizedcache );
	}

	public function testMemoizedCacheWritesToThePrimary()
	{
		$this->cache->put( self::KEY, self::VALUE );
		$this->assertEquals( self::VALUE, $this->primarycache->get( self::KEY ) ); 
	}

	public function testMemoizedCacheReadsFromMemoizedFirst()
	{
		$this->cache->put( self::KEY, self::VALUE );
		$this->memoizedcache->put( self::KEY, self::VALUE2 );
		$this->assertEquals( self::VALUE2, $this->cache->get( self::KEY ) );
	}

	public function testMemoizedCacheWritesToTheMemoizedOnRead()
	{
		$this->cache->put( self::KEY, self::VALUE );
		$this->cache->get( self::KEY );
		$this->primarycache->delete( self::KEY );
		$this->assertEquals( self::VALUE, $this->cache->get( self::KEY ) );
	}

	public function testMemoizedCacheDeletesFromMemoizedOnDelete()
	{
		$this->cache->put( self::KEY, self::VALUE );
		$this->cache->get( self::KEY );
		$this->cache->delete( self::KEY );
		$this->assertFalse( $this->cache->get( self::KEY ) );
	}

	public function testMemoizedCacheReturnsCurrentDataAfterOverwrite()
	{
		$this->cache->put( self::KEY, self::VALUE );
		$this->cache->get( self::KEY );
		$this->cache->put( self::KEY, self::VALUE2 );
		$this->assertEquals( self::VALUE2, $this->cache->get( self::KEY ) );
	}
	
	
}
