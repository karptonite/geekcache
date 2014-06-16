<?php
namespace Geek\Cache;

class ArrayCache implements IncrementableCache
{
	private $cache = array();
	private $maxputs;
	private $putcount = 0;

	public function __construct( $maxputs = null )
	{
		$this->maxputs = (int)$maxputs;
	}

	public function get( $key )
	{
		return array_key_exists( $key, $this->cache) ? $this->cache[$key] : false;
	}

	public function put( $key, $value, $ttl = null )
	{
		if( $this->putIsPermitted( $key ) )
		{
			$this->cache[$key] = $value;
			$this->putcount++;
		}
	}

	private function putIsPermitted( $key )
	{
		return !$this->maxputs || $this->putcount < $this->maxputs || array_key_exists( $key, $this->cache );
	}

	public function delete( $key )
	{
		unset( $this->cache[$key] );
	}

	public function increment( $key, $value = 1 )
	{
		$current = $this->get( $key );
		$this->cache[$key] = is_numeric( $current ) ? $current + $value : $value;
		return $this->cache[$key];
	}
}
