<?php
class MemoizedCounterTest extends BaseCounterTest
{
	private $primarycounter;

	public function setUp()
	{
		parent::setUp();
		$this->primarycounter  = new Geek\Cache\ArrayCache;
		$this->memoizedcounter = new Geek\Cache\ArrayCache;
		$this->counter         = new Geek\Cache\MemoizedCounter( $this->primarycounter, $this->memoizedcounter );
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

