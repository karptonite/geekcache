<?php
class CacheServiceProviderMemcacheTest extends CacheServiceProviderTest
{
    protected function getPersistentServiceProvider()
    {
        return new GeekCache\Provider\MemcacheServiceProvider($this->container);
    }

    public function testAddsMemcacheToContainer()
    {
        $this->assertInstanceOf('Memcache', $this->container['geekcache.memcache']);
    }

    public function testDefaultServersAdded()
    {
        $stats = $this->container['geekcache.memcache']->getExtendedStats();
        $this->assertEquals('localhost:11211', key($stats));
    }

    public function testDefaultServersOverrideable()
    {
        $this->container['geekcache.memcache.servers'] = array('127.0.0.1' =>  array(11211));
        $stats = $this->container['geekcache.memcache']->getExtendedStats();
        $this->assertEquals('127.0.0.1:11211', key($stats));
    }

    public function testMemcacheCounterRegistered()
    {
        $memcacheincrementablecache1 = $this->container['geekcache.persistentincrementablecache'];
        $memcacheincrementablecache2 = $this->container['geekcache.persistentincrementablecache'];
        $this->assertSame($memcacheincrementablecache1, $memcacheincrementablecache2);
        $this->assertInstanceOf('GeekCache\Cache\IncrementableMemcacheCache', $memcacheincrementablecache1);
    }
}
