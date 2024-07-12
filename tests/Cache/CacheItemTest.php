<?php
use Mockery as m;

class CacheItemTest extends PHPUnit\Framework\TestCase
{
    const KEY   = 'thekey';
    const VALUE = 'foobar';
    const KEY2   = 'thekey2';
    const VALUE2 = 'foobar2';
    const TTL = 5;

    private $cache;
    private $cacheitem;
    private $cacheitem2;

    public function tearDown(): void
    {
        m::close();
        parent::tearDown();
    }

    public function setUp(): void
    {
        parent::setUp();
        $this->cache = new GeekCache\Cache\ArrayCache;
        $this->cacheitem = new GeekCache\Cache\NormalCacheItem($this->cache, self::KEY, self::TTL);
        $this->cacheitem2 = new GeekCache\Cache\NormalCacheItem($this->cache, self::KEY2, self::TTL);
    }

    public function testCacheItemPutsAndGets()
    {
        $this->cacheitem->put(self::VALUE);
        $this->cacheitem2->put(self::VALUE2);

        $this->assertEquals(self::VALUE, $this->cache->get(self::KEY));
        $this->assertEquals(self::VALUE2, $this->cache->get(self::KEY2));
        $this->assertEquals(self::VALUE, $this->cacheitem->get());
        $this->assertEquals(self::VALUE2, $this->cacheitem2->get());
    }

    public function testCacheItemDeletes()
    {
        $this->cacheitem->put(self::VALUE);
        $this->cacheitem2->put(self::VALUE2);
        $this->cacheitem->delete();

        $this->assertFalse($this->cache->get(self::KEY));
        $this->assertEquals(self::VALUE2, $this->cache->get(self::KEY2));
        $this->assertFalse($this->cacheitem->get());
        $this->assertEquals(self::VALUE2, $this->cacheitem2->get());
    }

    public function testCacheItemReturnsFalseOnCacheMiss()
    {
        $this->assertFalse($this->cacheitem->get());
    }

    public function testCacheItemPassesTtlOnPut()
    {
        $cache = m::mock('GeekCache\Cache\Cache');
        $cache->shouldReceive('put')
            ->with(self::KEY, self::VALUE, self::TTL)
            ->once();

        $cacheitem = new GeekCache\Cache\NormalCacheItem($cache, self::KEY, self::TTL);
        $cacheitem->put(self::VALUE);
    }

    public function testCacheItemPassesRegenerator()
    {
        $regenerator = function() {
            return false;
        };

        $cache = m::mock('GeekCache\Cache\Cache');

        $cache->shouldReceive('get')
            ->with(self::KEY, $regenerator, self::TTL)
            ->once();

        $cacheitem = new GeekCache\Cache\NormalCacheItem($cache, self::KEY, self::TTL);
        $cacheitem->get($regenerator);
    }
}
