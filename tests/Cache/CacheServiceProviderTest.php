<?php
abstract class CacheServiceProviderTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		parent::setUp();
		$this->container = $this->getContainer();
		$this->sp = new Geek\Cache\CacheServiceProvider( $this->container );
		$this->msp = new Geek\Cache\MemcacheServiceProvider( $this->container );
		$this->sp->register();
		$this->msp->register();
	}
	
	public function testAddsMemcacheToContainer()
	{
		$this->assertInstanceOf( 'Memcache', $this->container['geekcache.memcache'] );
	}

	public function testDefaultServersAdded()
	{
		$stats = $this->container['geekcache.memcache']->getExtendedStats();
		$this->assertEquals( 'localhost:11211', key( $stats ) );
	}
	
	public function testDefaultServersOverrideable()
	{
		$this->container['geekcache.memcache.servers'] = array( '127.0.0.1' =>  array( 11211 ) );
		$stats = $this->container['geekcache.memcache']->getExtendedStats();
		$this->assertEquals( '127.0.0.1:11211', key( $stats ) );
	}

	public function testMemoReturnsArrayCaches()
	{
		$cache = $this->container['geekcache.local.memos'];
		$cache2 = $this->container['geekcache.local.memos'];
		$this->assertInstanceOf( 'Geek\Cache\ArrayCache', $cache );
		$this->assertSame( $cache, $cache2 );
	}

	public function testReturnsNullCachesIfNoLocalCacheIsSet()
	{
		$this->container['geekcache.nolocalcache'] = true;
		$cache = $this->container['geekcache.local.memos'];
		$this->assertInstanceOf( 'Geek\Cache\NullCache', $cache );
	}

	public function testLocalCachesRespectMaxSetting()
	{
		$this->container['geekcache.maxlocal.memos'] = 2;
		$cache = $this->container['geekcache.local.memos'];
		$cache->put( 'foo', 'bar' );
		$cache->put( 'foo2', 'bar2' );
		$cache->put( 'foo3', 'bar3' );

		$this->assertEquals( 'bar2', $cache->get( 'foo2' ) );
		$this->assertFalse( $cache->get( 'foo3' ) );
	}

	public function testMecacheCounterRegistered()
	{
		$memcachecounter1 = $this->container['geekcache.persistentcounter'];
		$memcachecounter2 = $this->container['geekcache.persistentcounter'];
		$this->assertSame( $memcachecounter1, $memcachecounter2 );
		$this->assertInstanceOf( 'Geek\Cache\MemcacheCounter', $memcachecounter1 );
	}
	

	public function testTagFactoryRegistered()
	{
		$tagfactory1 = $this->container['geekcache.tagfactory'];
		$tagfactory2 = $this->container['geekcache.tagfactory'];
		$this->assertSame( $tagfactory1, $tagfactory2 );
		$this->assertInstanceOf( 'Geek\Cache\TagFactory', $tagfactory1 );
	}
	
	public function testTagSetFactoryRegistered()
	{
		$tagsetfactory1 = $this->container['geekcache.tagsetfactory'];
		$tagsetfactory2 = $this->container['geekcache.tagsetfactory'];
		$this->assertSame( $tagsetfactory1, $tagsetfactory2 );
		$this->assertInstanceOf( 'Geek\Cache\TagSetfactory', $tagsetfactory1 );
	}

	public function testCacheBuilderRegistered()
	{
		$cachebuilder1 = $this->container['cachebuilder'];
		$cachebuilder2 = $this->container['cachebuilder'];
		$this->assertSame( $cachebuilder1, $cachebuilder2 );
		$this->assertInstanceOf( 'Geek\Cache\CacheBuilder', $cachebuilder1 );
	}

	public function testLocalCounter()
	{
		$counter1 = $this->container['geekcache.local.counter'];
		$counter2 = $this->container['geekcache.local.counter'];
		$this->assertSame( $counter1, $counter2 );
		$this->assertInstanceOf( 'Geek\Cache\ArrayCounter', $counter1 );
	}
	
	public function testLocalCounterNullWhenNoLocalcacheIsSet()
	{
		$this->container['geekcache.nolocalcache'] = true;
		$counter1 = $this->container['geekcache.local.counter'];
		$counter2 = $this->container['geekcache.local.counter'];
		$this->assertSame( $counter1, $counter2 );
		$this->assertInstanceOf( 'Geek\Cache\NullCache', $counter1 );
	}

	public function testCounterRegistered()
	{
		$counter1 = $this->container['geekcache.counter'];
		$counter2 = $this->container['geekcache.counter'];
		$this->assertNotSame( $counter1, $counter2 );
		$this->assertInstanceOf( 'Geek\Cache\MemoizedCounter', $counter1 );
		$this->assertInstanceOf( 'Geek\Cache\MemoizedCounter', $counter2 );
	}
	
	
}

