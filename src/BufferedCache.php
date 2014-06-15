<?php
namespace Geek\Cache;

/**
 * The memoized cache should not be long-lived. It does not respect TTL, 
 * and can go out of sync if the primary is changed in another process.
 *
 * It's intended use is buffering data retrieved over the course of a single
 * HTTP request.
 */
class BufferedCache extends CacheDecorator
{
	private $_memoizedCache;

	public function __construct( Cache $primaryCache, Cache $memoizedCache )
	{
		parent::__construct( $primaryCache );
		$this->_memoizedCache = $memoizedCache;
	}

	public function get( $key )
	{
		return $this->_memoizedCache->get( $key )?: $this->getAndBuffer( $key );
	}

	public function put( $key, $value, $ttl = null )
	{
		parent::put( $key, $value, $ttl );
		$this->_memoizedCache->put( $key, $value );
	}
	
	private function getAndBuffer( $key )
	{
		$value = parent::get( $key );
		$this->_memoizedCache->put( $key, $value );
		return $value;
	}

	public function delete( $key )
	{
		parent::delete( $key );
		$this->_memoizedCache->delete( $key );
	}
}
