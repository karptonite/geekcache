<?php

namespace GeekCache\Provider;

use GeekCache\Cache;

class MemcachedServiceProvider
{
    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function register()
    {
        $this->container->bind('geekcache.memcached', function ($c) {
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
        }, true);

        $this->container->bind('geekcache.persistentincrementablecache.unnamespaced', function ($c) {
            return new Cache\IncrementableStageableCache(new Cache\MemcachedCache($c['geekcache.memcached']));
        }, true);

        $this->container->bind('geekcache.persistentcache.unnamespaced', function ($c) {
            return new Cache\StageableCache(new Cache\MemcachedCache($c['geekcache.memcached']));
        }, true);
    }
}
