<?php
abstract class BaseIncrementableCacheTest extends PHPUnit_Framework_TestCase
{
	protected $cache;

	const KEY = 'foo';

	public function testIncrementSetsToOneForCacheNotFound()
	{
		$this->cache->increment( static::KEY );
		$this->assertEquals( 1, $this->cache->get( static::KEY ) );
	}

	public function testIncrementSetsToValueForCacheNotFound()
	{
		$this->cache->increment( static::KEY, 2 );
		$this->assertEquals( 2, $this->cache->get( static::KEY ) );
	}

	public function testIncrementSetsToOneForNonNumericCache()
	{
		$this->cache->put( static::KEY, "foo" );
		$this->cache->increment( static::KEY );
		$this->assertEquals( 1, $this->cache->get( static::KEY ) );
	}

	public function testIncrementIncrementsIntegerCache()
	{
		$this->cache->put( static::KEY, 4 );
		$this->cache->increment( static::KEY );
		$this->assertEquals( 5, $this->cache->get( static::KEY ) );
	}

	public function testIncrementReturnsNewValue()
	{
		$this->cache->put( static::KEY, 4 );
		$value = $this->cache->increment( static::KEY );
		$this->assertEquals( 5, $value );
	}

	public function testIncrementIncrementsIntegerByValue()
	{
		$this->cache->put( static::KEY, 4 );
		$this->cache->increment( static::KEY, -2 );
		$this->assertEquals( 2, $this->cache->get( static::KEY ) );
	}

	public function testIncrementIncrementsFloatCache()
	{
		$this->cache->put( static::KEY, 4.5 );
		$this->cache->increment( static::KEY );
		$this->assertEquals( 5.5, $this->cache->get( static::KEY ), 0.001 );
	}
}

