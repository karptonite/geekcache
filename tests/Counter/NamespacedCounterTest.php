<?php
class NamespacedCounterTest extends BaseCounterTest
{
	private $primarycounter;

	const CACHE_NAMESPACE = 'ns';

	public function setUp()
	{
		parent::setUp();
		$this->primarycounter  = new GeekCache\Cache\ArrayCounter;
		$this->counter         = new GeekCache\Cache\NamespacedCounter( $this->primarycounter, self::CACHE_NAMESPACE );
	}

}


