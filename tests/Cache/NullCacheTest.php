<?php
class NullCacheTest extends PHPUnit_Framework_TestCase
{
	protected $cache;
	const KEY = 'key';
	const VALUE = 'value';

	public function setUp()
	{
		$this->cache = new GeekCache\Cache\NullCache;
	}

	public function testPutAndGet()
	{
		$this->cache->put( self::KEY, self::VALUE );
		$this->assertFalse( $this->cache->get( self::KEY ) );
	}

	public function testIncrement()
	{
		$this->cache->increment( self::KEY, 2 );
	}
}
