<?php
use Mockery as m;

class CacheClearerTest extends PHPUnit\Framework\TestCase
{
    const KEY = 'theTag';
    private $container;
    private $clearer;

    public function setUp(): void
    {
        parent::setUp();
        $this->container = new Illuminate\Container\Container;
        $msp = new GeekCache\Provider\MemcachedServiceProvider($this->container);
        $sp = new GeekCache\Provider\CacheServiceProvider($this->container);
        $msp->register();
        $sp->register();

        $this->clearer = new GeekCache\Cache\CacheClearer(
            $this->container['geekcache.tagsetfactory'],
            $this->container['geekcache.persistentcache'],
            array($this->container['geekcache.local.memos'], $this->container['geekcache.local.tags'])
        );
    }

    public function tearDown(): void
    {
        m::close();
        parent::tearDown();
    }


    public function testClearTag()
    {
        $cache = $this->container['geekcache.cachebuilder']->addTags('foo','bar')->make('key');
        $cache->put('value');
        $this->assertEquals('value', $cache->get());

        $this->clearer->clearTags('bar');

        $this->assertFalse($cache->get());
    }

    public function testInvalidatesTagsArray()
    {
        $cache = $this->container['geekcache.cachebuilder']->addTags('foo','bar')->make('key');
        $cache->put('value');

        $this->clearer->clearTags(array('bar'));
        $this->assertFalse($cache->get());
    }

    public function testFlush()
    {
        $cache = $this->container['geekcache.cachebuilder']->memoize()->make('key');
        $cache->put('value');

        $this->clearer->flush();
        $this->assertFalse($cache->get());
    }

    public function testFlushLocal()
    {
        $cache = $this->container['geekcache.cachebuilder']->memoize()->make('key');
        $cache->put('value');
        $this->assertNotFalse($this->container['geekcache.local.memos']->get('key'));
        $this->clearer->flushLocal();

        $this->assertFalse($this->container['geekcache.local.memos']->get('key'));
    }
}
