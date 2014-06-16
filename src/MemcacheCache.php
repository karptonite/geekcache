<?php
namespace Geek\Cache;

class MemcacheCache implements Cache
{
	private $cache;

	public function __construct( \Memcache $cache )
	{
		$this->cache = $cache;
	}

	public function get( $key )
	{
		return $this->cache->get( $key );
	}

	public function put( $key, $value, $ttl = null )
	{
		return $this->cache->set( $key, $value, MEMCACHE_COMPRESSED, (int)$ttl );
	}

	public function delete( $key )
	{
		return $this->cache->delete( $key );
	}
}
	
