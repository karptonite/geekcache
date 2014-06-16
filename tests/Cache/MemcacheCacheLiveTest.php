<?php
class MemcacheCacheLiveTest extends BaseCacheTest
{

	public function setUp()
	{
		parent::setUp();
		$memcache = new Memcache();
		$connected = $memcache->connect('localhost', 11211);
		$this->cache = new Geek\Cache\MemcacheCache( $memcache );
		$memcache->flush();
	}
}
