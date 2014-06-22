<?php
use Mockery as m;

class CounterBuilderTest extends PHPUnit_Framework_TestCase
{
	public function prepareFullMockBuilder()
	{
		$this->counter         = m::mock( 'GeekCache\Cache\Counter' );
		$this->memocounter     = m::mock( 'GeekCache\Cache\Counter' );
		
		$this->builder = new GeekCache\Cache\CounterBuilder( $this->counter, $this->memocounter );
	}

	public function prepareArrayBuilder()
	{
		$this->counter = new GeekCache\Cache\ArrayCounter();
		$this->memocounter = new GeekCache\Cache\ArrayCounter();
		$this->builder = new GeekCache\Cache\CounterBuilder( $this->counter, $this->memocounter );
	}
	
	public function testBuildBasic()
	{
		$this->prepareFullMockBuilder();
		$this->counter->shouldReceive( 'get' )->with( 'foo' )->once();
		$counter = $this->builder->make();
		$counter->get('foo');
	}

	public function testMemoizeBuild()
	{
		$this->prepareFullMockBuilder();
		$this->memocounter->shouldReceive( 'get' )->with( 'foo' )->once()->andReturn( false );
		$this->memocounter->shouldReceive( 'put' )->with( 'foo', 2 )->once();
		$this->counter->shouldReceive( 'get' )->with( 'foo' )->once()->andReturn( 2 );
		$counter = $this->builder->memoize()->make();
		$this->assertInstanceOf( 'GeekCache\Cache\MemoizedCounter', $counter );
		$counter->get( 'foo' );
	}
}

