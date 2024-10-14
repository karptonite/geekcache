<?php

namespace GeekCache\Provider;

use GeekCache\Tag;
use GeekCache\Cache;

class CacheServiceProvider
{
    private $container;

    private static $default_maxlocal = array(
        'memos' => 1000,
        'tags'  => 5000
    );

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function register()
    {
        $this->registerLocalCache('memos');
        $this->registerLocalCache('tags');

        $this->container->bind('geekcache.persistentcache', function ($c) {
            return !empty($c['geekcache.namespace'])
                ? new Cache\NamespacedCache($c['geekcache.persistentcache.unnamespaced'], $c['geekcache.namespace'])
                : $c['geekcache.persistentcache.unnamespaced'];
        }, true);

        $this->container->bind('geekcache.persistentincrementablecache', function ($c) {
            return !empty($c['geekcache.namespace'])
                ? new Cache\IncrementableNamespacedCache(
                    $c['geekcache.persistentincrementablecache.unnamespaced'],
                    $c['geekcache.namespace']
                ) : $c['geekcache.persistentincrementablecache.unnamespaced'];
        }, true);

        $this->container->bind('geekcache.local.incrementablecache', function ($c) {
            return !empty($c['geekcache.nolocalcache']) ? new Cache\NullCache() : new Cache\IncrementableArrayCache();
        }, true);

        $this->container->bind('geekcache.tagfactory', function ($c) {
            $cache = new Cache\MemoizedCache($c['geekcache.persistentcache'], $c['geekcache.local.tags']);
            return new Tag\TagFactory($cache);
        }, true);

        $this->container->bind('geekcache.tagsetfactory', function ($c) {
            return new Tag\TagSetFactory($c['geekcache.tagfactory']);
        }, true);

        $this->container->bind('geekcache.cachebuilder', function ($c) {
            return new Cache\CacheBuilder(
                $c['geekcache.persistentcache'],
                $c['geekcache.local.memos'],
                $c['geekcache.tagsetfactory']
            );
        }, true);

        $this->container->bind('geekcache.counterbuilder', function ($c) {
            return new \GeekCache\Counter\CounterBuilder(
                $c['geekcache.persistentincrementablecache'],
                $c['geekcache.local.incrementablecache']
            );
        }, true);

        $this->container->bind('geekcache.clearer', function ($c) {
            return new \GeekCache\Cache\CacheClearer(
                $c['geekcache.tagsetfactory'],
                $c['geekcache.persistentcache'],
                array($c['geekcache.local.tags'], $c['geekcache.local.memos'])
            );
        }, true);
    }

    private function registerLocalCache($name)
    {
        $default_max = self::$default_maxlocal[$name];
        $this->container->bind('geekcache.local.'.$name, function ($c) use ($name, $default_max) {
            $max = isset($c['geekcache.maxlocal.' . $name]) ? $c['geekcache.maxlocal.'.$name] : $default_max;
            //If the process will last longer than a page load, make sure to set geekcache.nolocalcache to true
            //to avoid keeping a potentially stale local cache.
            return !empty($c['geekcache.nolocalcache']) ? new Cache\NullCache() : new Cache\ArrayCache($max);
        }, true);
    }
}
