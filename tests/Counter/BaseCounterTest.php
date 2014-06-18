<?php
abstract class BaseCounterTest extends PHPUnit_Framework_TestCase
{
	protected $counter;

	const KEY = 'foo';

	public function testIncrementSetsToOneForCacheNotFound()
	{
		$this->counter->increment( static::KEY );
		$this->assertEquals( 1, $this->counter->get( static::KEY ) );
	}

	public function testIncrementSetsToValueForCacheNotFound()
	{
		$this->counter->increment( static::KEY, 2 );
		$this->assertEquals( 2, $this->counter->get( static::KEY ) );
	}

	public function testIncrementSetsToOneForNonNumericCache()
	{
		$this->counter->put( static::KEY, "foo" );
		$this->counter->increment( static::KEY );
		$this->assertEquals( 1, $this->counter->get( static::KEY ) );
	}

	public function testIncrementIncrementsIntegerCache()
	{
		$this->counter->put( static::KEY, 4 );
		$this->counter->increment( static::KEY );
		$this->assertEquals( 5, $this->counter->get( static::KEY ) );
	}

	public function testIncrementReturnsNewValue()
	{
		$this->counter->put( static::KEY, 4 );
		$value = $this->counter->increment( static::KEY );
		$this->assertEquals( 5, $value );
	}

	public function testIncrementIncrementsIntegerByValue()
	{
		$this->counter->put( static::KEY, 4 );
		$this->counter->increment( static::KEY, -2 );
		$this->assertEquals( 2, $this->counter->get( static::KEY ) );
	}

	public function testIncrementDoesNotGoNegative()
	{
		$this->counter->put( static::KEY, 2 );
		$this->counter->increment( static::KEY, -4 );
		$this->assertEquals( 0, $this->counter->get( static::KEY ) );
	}

	public function testIncrementDoesNotCreateRecordForNegativeValue()
	{
		$result = $this->counter->increment( static::KEY, -4 );
		$this->assertFalse( $this->counter->get( static::KEY ) );
		$this->assertFalse( $result );
	}
	
	public function testIncrementIncrementsFloatCache()
	{
		$this->counter->put( static::KEY, 4.5 );
		$this->counter->increment( static::KEY );
		$this->assertEquals( 5.5, $this->counter->get( static::KEY ), 0.001 );
	}
}

