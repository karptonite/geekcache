<?php
namespace Geek\Cache;

class NullCache implements Cache
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
}
