<?php
namespace Geek\Cache;

class SerializedCache extends CacheDecorator
{
	public function get( $key )
	{
		return unserialize( parent::get( $key ) );
	}

	public function put( $key, $value, $ttl = null )
	{
		return parent::put( $key, serialize( $value ), $ttl );
	}
}	
