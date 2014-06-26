<?php
namespace GeekCache\Cache;

class NullCache implements Cache, IncrementableCache
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

    public function clear()
    {
        return true;
    }

    public function increment( $key, $value = 1 )
    {
        return null;
    }
}
