<?php
abstract class CacheServiceProviderTest extends PHPUnit\Framework\TestCase
{
    protected $sp;
    protected $msp;
    protected $container;
    public function setUp(): void
    {
        parent::setUp();
        $this->container = $this->getContainer();
        $this->sp = new GeekCache\Provider\CacheServiceProvider($this->container);
        $this->msp = $this->getPersistentServiceProvider();
        $this->sp->register();
        $this->msp->register();
    }

    protected function getContainer()
    {
        return new Illuminate\Container\Container();
    }

    protected function getPersistentServiceProvider()
    {
        return new GeekCache\Provider\MemcachedServiceProvider($this->container);
    }

    public function testMemoReturnsArrayCaches()
    {
        $cache = $this->container['geekcache.local.memos'];
        $cache2 = $this->container['geekcache.local.memos'];
        $this->assertInstanceOf('GeekCache\Cache\ArrayCache', $cache);
        $this->assertSame($cache, $cache2);
    }

    public function testReturnsNullCachesIfNoLocalCacheIsSet()
    {
        $this->container['geekcache.nolocalcache'] = true;
        $cache = $this->container['geekcache.local.memos'];
        $this->assertInstanceOf('GeekCache\Cache\NullCache', $cache);
    }

    public function testLocalCachesRespectMaxSetting()
    {
        $this->container['geekcache.maxlocal.memos'] = 2;
        $cache = $this->container['geekcache.local.memos'];
        $cache->put('foo', 'bar');
        $cache->put('foo2', 'bar2');
        $cache->put('foo3', 'bar3');

        $this->assertEquals('bar2', $cache->get('foo2'));
        $this->assertFalse($cache->get('foo3'));
    }

    public function testTagFactoryRegistered()
    {
        $tagfactory1 = $this->container['geekcache.tagfactory'];
        $tagfactory2 = $this->container['geekcache.tagfactory'];
        $this->assertSame($tagfactory1, $tagfactory2);
        $this->assertInstanceOf('GeekCache\Tag\TagFactory', $tagfactory1);
    }

    public function testTagSetFactoryRegistered()
    {
        $tagsetfactory1 = $this->container['geekcache.tagsetfactory'];
        $tagsetfactory2 = $this->container['geekcache.tagsetfactory'];
        $this->assertSame($tagsetfactory1, $tagsetfactory2);
        $this->assertInstanceOf('GeekCache\Tag\TagSetfactory', $tagsetfactory1);
    }

    public function testCacheBuilderRegistered()
    {
        $cachebuilder1 = $this->container['geekcache.cachebuilder'];
        $cachebuilder2 = $this->container['geekcache.cachebuilder'];
        $this->assertSame($cachebuilder1, $cachebuilder2);
        $this->assertInstanceOf('GeekCache\Cache\CacheBuilder', $cachebuilder1);
    }

    public function testLocalIncrementableCache()
    {
        $incrementablecache1 = $this->container['geekcache.local.incrementablecache'];
        $incrementablecache2 = $this->container['geekcache.local.incrementablecache'];
        $this->assertSame($incrementablecache1, $incrementablecache2);
        $this->assertInstanceOf('GeekCache\Cache\IncrementableArrayCache', $incrementablecache1);
    }

    public function testLocalIncrementableCacheNullWhenNoLocalcacheIsSet()
    {
        $this->container['geekcache.nolocalcache'] = true;
        $cache1 = $this->container['geekcache.local.incrementablecache'];
        $cache2 = $this->container['geekcache.local.incrementablecache'];
        $this->assertSame($cache1, $cache2);
        $this->assertInstanceOf('GeekCache\Cache\NullCache', $cache1);
    }

    public function testCounterBuilderRegistered()
    {
        $builder1 = $this->container['geekcache.counterbuilder'];
        $builder2 = $this->container['geekcache.counterbuilder'];
        $this->assertSame($builder1, $builder2);
        $this->assertInstanceOf('GeekCache\Counter\CounterBuilder', $builder1);
        $this->assertInstanceOf('GeekCache\Counter\CounterBuilder', $builder2);
    }

    public function testClearerRegistered()
    {
        $this->assertInstanceOf('GeekCache\Cache\CacheClearer', $this->container['geekcache.clearer']);
    }

    public function testNamespaceAddedToCacheIfSet()
    {
        $this->container['geekcache.namespace'] = 'foo';
        $this->assertInstanceOf('GeekCache\Cache\NamespacedCache', $this->container['geekcache.persistentcache']);
    }

    public function testPersistentCacheCaches()
    {
        $cache = $this->container['geekcache.persistentcache'];
        $cache->put('foo', 'bar');

        $this->assertEquals('bar', $cache->get('foo'));
    }

    public function testNamespaceAddedToCounterIfSet()
    {
        $this->container['geekcache.namespace'] = 'foo';
        $this->assertInstanceOf('GeekCache\Cache\IncrementableNamespacedCache', $this->container['geekcache.persistentincrementablecache']);
    }
}
