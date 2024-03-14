<?php
namespace GeekCache\Cache;

abstract class CacheDecorator extends AbstractBaseCache implements Cache
{
    private $cache;

    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }

    public function getMulti(array $keys)
    {
        return $this->cache->getMulti( $keys );
    }
    
    public function get($key, callable $regenerator = null, $ttl = null)
    {
        return $this->cache->get($key, $regenerator, $ttl);
    }

    public function put($key, $value, $ttl = null)
    {
        return $this->cache->put($key, $value, $ttl);
    }

    public function delete($key)
    {
        return $this->cache->delete($key);
    }

    public function clear()
    {
        return $this->cache->clear();
    }
}
