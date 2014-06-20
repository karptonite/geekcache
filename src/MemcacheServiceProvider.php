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
			$persistent = isset( $this->container['geekcache.memcache.persistent'] )
				? $this->container['geekcache.memcache.persistent']
				: 1;
			
			$memcache = new \Memcache();
			$servers = $this->getServers();
			foreach ($servers as $ip => $ports ) 
				foreach( $ports as $port )
					$memcache->addServer( $ip, $port, $persistent );
				
			return $memcache;
		});

		$this->container['geekcache.persistentcache'] = $this->container->share( function( $c ){
			return new MemcacheCache( $c['geekcache.memcache'] );
		} );
	}

	private function getServers()
	{
		return isset( $this->container['geekcache.memcache.servers'] )
			? $this->container['geekcache.memcache.servers'] 
			: array( 'localhost' => array( 11211 ) );
	}
}

