<?php
use Mockery as m;

class FacadeTest extends PHPUnit_Framework_TestCase
{
    const KEY   = 'thekey';
    const KEY2   = 'thekey2';
    const VALUE = 10;
    const VALUE2 = 5;
    const TTL = 20;
    private $cache;

    public function setUp()
    {
        parent::setUp();
        $container = new Illuminate\Container\Container;
        $msp = new GeekCache\Provider\MemcacheServiceProvider($container);
        $sp = new GeekCache\Provider\CacheServiceProvider($container);
        $msp->register();
        $sp->register();

        $this->facade = new GeekCache\Facade\CacheFacade(
            $container['geekcache.cachebuilder'],
            $container['geekcache.counterbuilder'],
            $container['geekcache.tagsetfactory']);
    }

    public function testMakesCache()
    {
        $cacheitem = $this->facade->cache()->make('foo');
        $this->assertInstanceOf( 'GeekCache\Cache\CacheItem', $cacheitem );
    }

    public function testMakesCounter()
    {
        $counter = $this->facade->counter()->make('foo');
        $this->assertInstanceOf( 'GeekCache\Counter\Counter', $counter );
    }

    public function testInvalidatesTags()
    {
        $cache = $this->facade->cache()->addTags('foo', 'bar')->make('key');
        $cache->put('value');

        $this->facade->clearTags('bar');
    }

    public function testInvalidatesTagsArray()
    {
        $cache = $this->facade->cache()->addTags('foo', 'bar')->make('key');
        $cache->put('value');

        $this->facade->clearTags(array('bar'));
    }
}

