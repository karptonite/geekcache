<?php
namespace Geek\Cache;

class NullCache implements IncrementableCache
{
	public function get( $key )
	{
		return false;
	}

	public function put( $key, $value, $ttl = null )
	{
		return null;
	}

	public function delete( $key )
	{
		return null;
	}

	public function increment( $key, $value = 1 )
	{
		return null;
	}
}
