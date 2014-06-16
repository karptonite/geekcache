<?php
class ArrayCacheCounterTest extends BaseCounterTest
{
	public function setUp()
	{
		parent::setUp();
		$this->counter = new Geek\Cache\ArrayCache;
	}
}
