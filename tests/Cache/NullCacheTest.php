<?php
class NullCacheTest extends PHPUnit_Framework_TestCase
{
	protected $cache;
	const KEY = 'key';
	const VALUE = 'value';

	public function setUp()
	{
		$this->cache = new Geek\Cache\NullCache;
	}

	public function testPutAndGet()
	{
		$this->cache->put( self::KEY, self::VALUE );
		$this->assertFalse( $this->cache->get( self::KEY ) );
	}
}
