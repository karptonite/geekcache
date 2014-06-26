<?php
use Mockery as m;

class MemcacheCacheTest extends PHPUnit_Framework_TestCase
{
    const KEY = 'foo';
    const VALUE = 'bar';
    const TTL = 10;

    public function setUp()
    {
        parent::setUp();
        $this->mock = m::mock('Memcache');
        $this->cache = new GeekCache\Cache\MemcacheCache($this->mock);
    }

    public function testGet()
    {
        $this->mock->shouldReceive('get')
            ->with(self::KEY)
            ->once()
            ->andReturn(self::VALUE);

        $this->assertEquals(self::VALUE, $this->cache->get(self::KEY));
    }

    public function testDelete()
    {
        $this->mock->shouldReceive('delete')
            ->with(self::KEY, 0)
            ->once()
            ->andReturn(true);

        $this->assertEquals(true, $this->cache->delete(self::KEY));
    }

    public function testPut()
    {
        $this->mock->shouldReceive('set')
            ->with(self::KEY, self::VALUE, MEMCACHE_COMPRESSED, self::TTL)
            ->once()
            ->andReturn(true);

        $this->assertEquals(true, $this->cache->put(self::KEY, self::VALUE, self::TTL));
    }
}
