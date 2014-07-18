<?php
namespace GeekCache\Cache;

class MemcachedCache extends AbstractBaseCache implements Cache
{
    private $cache;

    public function __construct(\Memcached $cache)
    {
        $this->cache = $cache;
    }

    public function get($key, callable $regenerator = null, $ttl = null)
    {
        $result = $this->cache->get($key);
        return $result !== false ? $result : $this->regenerate($key, $regenerator, $ttl);
    }

    public function put($key, $value, $ttl = null)
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
}
