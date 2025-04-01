<?php
use Mockery as m;

class NamespacedCacheTest extends BaseCacheTestAbstract
{
    private $parentcache;

    const CACHE_NAMESPACE = "ns";
    const TTL = 5;

    public function setUp(): void
    {
        parent::setUp();
        $this->parentcache = new GeekCache\Cache\ArrayCache;
        $this->cache = new GeekCache\Cache\NamespacedCache($this->parentcache, self::CACHE_NAMESPACE);
    }

    public function tearDown(): void
    {
        m::close();
        parent::tearDown();
    }
    public function testNamespaceAdded()
    {
        $this->cache->put('foo', 'bar');
        $this->assertEquals('bar', $this->parentcache->get(self::CACHE_NAMESPACE . '_foo'));
    }

    public function testPassesTTLWithRegenerator()
    {
        $parentCache = m::mock('GeekCache\Cache\Cache');

        $regenerator = function () {
            return false;
        };

        $nskey = self::CACHE_NAMESPACE . '_' . self::KEY;
        $parentCache->shouldReceive('get')
            ->with($nskey, $regenerator, self::TTL)
            ->once();

        $cache = new GeekCache\Cache\NamespacedCache($parentCache, self::CACHE_NAMESPACE);

        $cache->get(self::KEY, $regenerator, self::TTL);
    }
}
