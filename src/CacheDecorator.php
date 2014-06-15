<?php
namespace Geek\Cache;

abstract class CacheDecorator implements Cache
{
	private $_cache;

	public function __construct( Cache $cache )
	{
		$this->_cache = $cache;
	}

	public function get( $key )
	{
		return $this->_cache->get( $key );
	}

	public function put( $key, $value, $ttl = null )
	{
		return $this->_cache->put( $key, $value, $ttl );
	}

	public function delete( $key )
	{
		return $this->_cache->delete( $key );
	}
}	

