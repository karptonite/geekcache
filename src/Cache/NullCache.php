<?php
namespace GeekCache\Cache;

class NullCache implements Cache, IncrementableCache
{
    public function get($key, callable $regenerator = null, $ttl = null)
    {
        return false;
    }
    
    public function getMulti(array $keys)
    {
        $result = [];
        foreach( $keys as $key) {
            $result[$key] = false;
        }
        return $result;
    }

    public function put($key, $value, $ttl = null)
    {
        return null;
    }

    public function delete($key)
    {
        return null;
    }

    public function clear()
    {
        return true;
    }

    public function increment($key, $value = 1, $ttl = null)
    {
        return null;
    }
}
