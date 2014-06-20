<?php
namespace GeekCache\Cache;

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
		//passing an explicit 0 for timeout because of this issue:
		//http://stackoverflow.com/questions/4745345/how-do-i-stop-phpmemcachedelete-from-producing-a-client-error
		return $this->cache->delete( $key, 0 );
	}
}
