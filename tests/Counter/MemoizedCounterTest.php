<?php
class MemoizedCounterTest extends BaseCounterTest
{
	private $primarycounter;

	public function setUp()
	{
		parent::setUp();
		$this->primarycounter  = new GeekCache\Cache\ArrayCounter;
		$this->memoizedcache   = new GeekCache\Cache\ArrayCache;
		$this->counter         = new GeekCache\Cache\MemoizedCounter( $this->primarycounter, $this->memoizedcache );
	}

	public function testMemoizedCounterIncrementsThePrimary()
	{
		$this->counter->put( self::KEY, 3 );
		$this->counter->increment( self::KEY, 2 );
		$this->assertEquals( 5, $this->primarycounter->get( self::KEY ) );
	}

	public function testMemoizedCounterIncrementsWhenItHasNotRead()
	{
		$this->primarycounter->put( self::KEY, 3 );
		$this->counter->increment( self::KEY, 2 );
		$this->assertEquals( 5, $this->counter->get( self::KEY ) );
	}
}

