<?php
namespace Geek\Cache;
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

		$this->container['geekcache.tagfactory'] = $this->container->share( function($c){
			$cache = new MemoizedCache( $c['geekcache.persistentcache'], $c['geekcache.local.tags'] );
			return new TagFactory( $cache );
		} );

		$this->container['geekcache.tagsetfactory'] = $this->container->share( function($c){
			return new TagSetFactory( $c['geekcache.tagfactory'] );
		} );

		$this->container['cachebuilder'] = $this->container->share( function($c){
			return new CacheBuilder( $c['geekcache.persistentcache'], $c['geekcache.local.memos'], $c['geekcache.tagsetfactory'] );
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
