<?php
namespace GeekCache\Provider;

use GeekCache\Cache;

class MemcachedServiceProvider
{
    public function __construct($container)
    {
        $this->container = $container;
    }

    public function register()
    {
        $this->container['geekcache.memcached'] = $this->container->share(function ($c) {
            $memcached = new \Memcached();

            $servers = isset($c['geekcache.memcache.servers'])
                ? $c['geekcache.memcache.servers']
                : array('localhost' => array(11211));

            foreach ($servers as $ip => $ports) {
                foreach ($ports as $port) {
                    $flatServers[] = array($ip, (int)$port);
                }
            };

            $memcached->addServers($flatServers);
            return $memcached;
        });

        $this->container['geekcache.persistentincrementablecache.unnamespaced'] = $this->container->share(function ($c) {
            return new Cache\IncrementableMemcachedCache($c['geekcache.memcached']);
        });

        $this->container['geekcache.persistentcache.unnamespaced'] = $this->container->share(function ($c) {
            return new Cache\MemcachedCache($c['geekcache.memcached']);
        });
    }
}
