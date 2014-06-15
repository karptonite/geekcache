<?php
namespace Geek\Cache;

interface Cache
{
	public function get( $key );
	public function put( $key, $value, $ttl = null );
	public function delete( $key );
}
