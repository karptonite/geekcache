<?php
namespace GeekCache\Cache;

abstract class CacheDecorator implements Cache
{
	private $cache;

	public function __construct( Cache $cache )
	{
		$this->cache = $cache;
	}

	public function get( $key )
	{
		return $this->cache->get( $key );
	}

	public function put( $key, $value, $ttl = null )
	{
		return $this->cache->put( $key, $value, $ttl );
	}

	public function delete( $key )
	{
		return $this->cache->delete( $key );
	}
}	

