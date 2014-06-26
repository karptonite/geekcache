<?php
namespace GeekCache\Cache;
class CacheServiceProvider
{
        static $default_maxlocal = array( 
                'memos' => 1000,
                'tags'  => 5000
        );

        function __construct( $container )
        {
                $this->container = $container;
        }

        public function register()
        {
                $this->registerLocalCache( 'memos' );
                $this->registerLocalCache( 'tags' );

                $this->container['geekcache.persistentcache'] = $this->container->share( function( $c ){
                        return !empty( $c['geekcache.namespace'] )
                                ? new NamespacedCache( $c['geekcache.persistentcache.unnamespaced'], $c['geekcache.namespace'] )
                                : $c['geekcache.persistentcache.unnamespaced'];
                } );

                $this->container['geekcache.persistentincrementablecache'] = $this->container->share( function( $c ){
                        return !empty( $c['geekcache.namespace'] )
                                ? new NamespacedIncrementableCache( $c['geekcache.persistentincrementablecache.unnamespaced'], $c['geekcache.namespace'] )
                                : $c['geekcache.persistentincrementablecache.unnamespaced'];
                } );

                $this->container['geekcache.local.incrementablecache'] = $this->container->share( function( $c ){
                        return !empty( $c['geekcache.nolocalcache'] ) ? new NullCache : new ArrayIncrementableCache();
                } );

                $this->container['geekcache.tagfactory'] = $this->container->share( function($c){
                        $cache = new MemoizedCache( $c['geekcache.persistentcache'], $c['geekcache.local.tags'] );
                        return new TagFactory( $cache );
                } );

                $this->container['geekcache.tagsetfactory'] = $this->container->share( function($c){
                        return new TagSetFactory( $c['geekcache.tagfactory'] );
                } );

                $this->container['geekcache.cachebuilder'] = $this->container->share( function($c){
                        return new CacheBuilder( $c['geekcache.persistentcache'], $c['geekcache.local.memos'], $c['geekcache.tagsetfactory'] );
                } );
        
                $this->container['geekcache.counterbuilder'] = $this->container->share( function( $c ){
                        return new CounterBuilder( $c['geekcache.persistentincrementablecache'], $c['geekcache.local.incrementablecache'] );
                } );
        }

        private function registerLocalCache( $name )
        {
                $default_max = self::$default_maxlocal[$name];
                $this->container['geekcache.local.'.$name] = $this->container->share( function( $c ) use( $name, $default_max ){
                        $max = isset( $c['geekcache.maxlocal.' . $name] ) ? $c['geekcache.maxlocal.'.$name] : $default_max;
                        //If the process will last longer than a page load, make sure to set geekcache.nolocalcache to true
                        //to avoid keeping a potentially stale local cache.
                        return !empty( $c['geekcache.nolocalcache'] ) ? new NullCache : new ArrayCache( $max );
                } );
        }
}
