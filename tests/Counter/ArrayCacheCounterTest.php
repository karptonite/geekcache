<?php
class ArrayCacheCounterTest extends BaseCounterTest
{
	public function setUp()
	{
		parent::setUp();
		$this->counter = new GeekCache\Cache\ArrayCounter;
	}
}
