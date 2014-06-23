<?php
use Mockery as m;

class CounterBuilderTest extends PHPUnit_Framework_TestCase
{
	public function prepareFullMockBuilder()
	{
		$this->cache         = m::mock( 'GeekCache\Cache\IncrementableCache' );
		$this->memocache     = m::mock( 'GeekCache\Cache\IncrementableCache' );
		
		$this->builder = new GeekCache\Cache\CounterBuilder( $this->cache, $this->memocache );
	}

	public function prepareArrayBuilder()
	{
		$this->cache = new GeekCache\Cache\ArrayIncrementableCache();
		$this->memocache = new GeekCache\Cache\ArrayIncrementableCache();
		$this->builder = new GeekCache\Cache\CounterBuilder( $this->cache, $this->memocache );
	}
	
	public function testBuildBasic()
	{
		$this->prepareFullMockBuilder();
		$this->cache->shouldReceive( 'get' )->with( 'foo' )->once();
		$cache = $this->builder->make();
		$cache->get('foo');
	}

	public function testMemoizeBuild()
	{
		$this->prepareFullMockBuilder();
		$this->memocache->shouldReceive( 'get' )->with( 'foo' )->once()->andReturn( false );
		$this->memocache->shouldReceive( 'put' )->with( 'foo', 2 )->once();
		$this->cache->shouldReceive( 'get' )->with( 'foo' )->once()->andReturn( 2 );
		$cache = $this->builder->memoize()->make();
		$this->assertInstanceOf( 'GeekCache\Cache\MemoizedIncrementableCache', $cache );
		$cache->get( 'foo' );
	}
}

