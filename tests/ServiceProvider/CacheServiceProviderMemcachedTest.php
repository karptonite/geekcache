<?php
class CacheServiceProviderMemcachedTest extends CacheServiceProviderTestAbstract
{
    use \DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;
    protected function getPersistentServiceProvider()
    {
        return new GeekCache\Provider\MemcachedServiceProvider($this->container);
    }

    public function testAddsMemcacheToContainer()
    {
        $this->assertInstanceOf('Memcached', $this->container['geekcache.memcached']);
    }

    public function testDefaultServersAdded()
    {
        $servers = $this->container['geekcache.memcached']->getServerList();
        $this->assertArraySubset([['host' => 'localhost', 'port'=>11211]], $servers);
    }

    public function testDefaultServersOverrideable()
    {
        $this->container['geekcache.memcache.servers'] = array(
            'localhost' => array(11211),
            '127.0.0.1' =>  array(11211),
        );

        $servers = $this->container['geekcache.memcached']->getServerList();
        $expected = [
            ['host' => 'localhost', 'port'=>11211],
            ['host' => '127.0.0.1', 'port'=>11211],
        ];

        $this->assertArraySubset($expected, $servers);
    }

    public function testMemcacheCounterRegistered()
    {
        $memcacheincrementablecache1 = $this->container['geekcache.persistentincrementablecache'];
        $memcacheincrementablecache2 = $this->container['geekcache.persistentincrementablecache'];
        $this->assertSame($memcacheincrementablecache1, $memcacheincrementablecache2);
        $this->assertInstanceOf('GeekCache\Cache\IncrementableStageableCache', $memcacheincrementablecache1);
    }
}
