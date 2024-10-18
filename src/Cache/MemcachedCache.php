<?php

namespace GeekCache\Cache;

class MemcachedCache implements MultiGetCache, IncrementableCacheBackend
{
    private \Memcached $cache;
    public function __construct(\Memcached $cache)
    {
        $this->cache = $cache;
    }

    public function get($key, callable $regenerator = null, $ttl = 0)
    {
        return $this->cache->get($key);
        // if we have a pending result, use that
    }

    public function getMulti(array $keys):array
    {
        $result = $this->cache->getMulti($keys);
        return is_array($result) ? $result : [];
    }

    public function put($key, $value, $ttl = 0)
    {
        return $this->cache->set($key, $value, (int)$ttl);
    }

    public function delete($key)
    {
        return $this->cache->delete($key);
    }

    public function clear()
    {
        return $this->cache->flush();
    }

    public function add($key, $value, $ttl = 0)
    {
        $this->cache->add($key, $value, $ttl);
    }

    public function increment($key, $value):false|int
    {
        return $this->cache->increment($key, $value);
    }

    public function decrement($key, $value):false|int
    {
        return $this->cache->decrement($key, $value);
    }
}
