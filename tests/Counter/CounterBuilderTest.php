<?php
use Mockery as m;

class CounterBuilderTest extends PHPUnit_Framework_TestCase
{
    public function prepareFullMockBuilder()
    {
        $this->cache         = m::mock('GeekCache\Cache\IncrementableCache');
        $this->memocache     = m::mock('GeekCache\Cache\IncrementableCache');

        $this->builder = new GeekCache\Counter\CounterBuilder($this->cache, $this->memocache);
    }

    public function prepareArrayBuilder()
    {
        $this->cache = new GeekCache\Cache\IncrementableArrayCache();
        $this->memocache = new GeekCache\Cache\IncrementableArrayCache();
        $this->builder = new GeekCache\Counter\CounterBuilder($this->cache, $this->memocache);
    }

    public function testBuildBasic()
    {
        $this->prepareFullMockBuilder();
        $this->cache->shouldReceive('get')->with('foo', null, null)->once();
        $cache = $this->builder->make('foo');
        $cache->get();
    }

    public function testMemoizeBuild()
    {
        $this->prepareFullMockBuilder();
        $this->memocache->shouldReceive('get')->with('foo')->once()->andReturn(false);
        $this->memocache->shouldReceive('put')->with('foo', 2)->once();
        $this->cache->shouldReceive('get')->with('foo', null, null)->once()->andReturn(2);
        $counter = $this->builder->memoize()->make('foo');
        $this->assertItemCacheInstanceOf('GeekCache\Cache\IncrementableMemoizedCache', $counter);
        $counter->get();
    }

    private function assertItemCacheInstanceOf($type, GeekCache\Counter\Counter $counter)
    {
        $class = new ReflectionClass('GeekCache\Counter\NormalCounter');
        $property = $class->getProperty('incrementablecache');
        $property->setAccessible(true);
        $this->assertInstanceOf($type, $property->getValue($counter));
    }
}
