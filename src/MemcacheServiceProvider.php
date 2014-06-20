<?php
namespace Geek\Cache;
class MemcacheServiceProvider
{
	function __construct( $container )
	{
		$this->container = $container;
	}

	public function register()
	{
		$this->container['geekcache.memcache'] = $this->container->share( function( $c )
		{
			$persistent = isset( $c['geekcache.memcache.persistent'] )
				? $c['geekcache.memcache.persistent']
				: 1;
			
			$memcache = new \Memcache();

			$servers = isset( $c['geekcache.memcache.servers'] )
				? $c['geekcache.memcache.servers'] 
				: array( 'localhost' => array( 11211 ) );

			foreach ($servers as $ip => $ports ) 
				foreach( $ports as $port )
					$memcache->addServer( $ip, $port, $persistent );
				
			return $memcache;
		});

		$this->container['geekcache.persistentcounter'] = $this->container->share( function( $c ){
			return new MemcacheCounter( $c['geekcache.memcache'] );
		} );

		$this->container['geekcache.persistentcache'] = $this->container->share( function( $c ){
			return new MemcacheCache( $c['geekcache.memcache'] );
		} );
	}
}

