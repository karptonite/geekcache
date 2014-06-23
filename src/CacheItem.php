<?php
namespace GeekCache\Cache;

class CacheItem
{
	private $cache;
	private $key;
	private $ttl;

	public function __construct( Cache $cache, $key, $ttl = null )
	{
		$this->cache = $cache;
		$this->key = $key;
		$this->ttl = $ttl;
	}
	
	public function get()
	{
		return $this->cache->get( $this->key );
	}

	public function put( $value )
	{
		return $this->cache->put( $this->key, $value, $this->ttl );
	}
	
	public function delete()
	{
		return $this->cache->delete( $this->key );
	}
}
