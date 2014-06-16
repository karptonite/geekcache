<?php
class MemcacheCacheLiveTest extends BaseCacheTest
{

	public function setUp()
	{
		parent::setUp();
		$memcache = new Memcache();
		$memcache->connect('localhost', 11211);
		$memcache->flush();
		$this->cache = new Geek\Cache\MemcacheCache( $memcache );
	}
}
