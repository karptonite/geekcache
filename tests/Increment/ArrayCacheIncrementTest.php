<?php
class ArrayCacheIncrementTest extends BaseIncrementableCacheTest
{
	public function setUp()
	{
		parent::setUp();
		$this->cache = new Geek\Cache\ArrayCache;
	}
}
