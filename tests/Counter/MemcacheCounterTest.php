<?php
class MemcacheCounterTest extends BaseCounterTest
{
	public function setUp()
	{
		parent::setUp();
		$memcache = new Memcache();
		$memcache->connect('localhost', 11211);
		$memcache->flush();
		$this->counter = new Geek\Cache\MemcacheCounter( $memcache );
	}
}

