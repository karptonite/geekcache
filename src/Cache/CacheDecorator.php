<?php

namespace GeekCache\Cache;

abstract class CacheDecorator extends AbstractBaseCache implements Cache
{
    private Cache $cache;

    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }

    public function get($key, callable $regenerator = null, $ttl = 0)
    {
        return $this->cache->get($key, $regenerator, $ttl);
    }

    public function stage(string $key): void
    {
        $this->cache->stage($key);
    }
    
    public function unstage(string $key): void
    {
        $this->cache->unstage($key);
    }

    public function put($key, $value, $ttl = 0)
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
    
    public function getGetCount(): int
    {
        return $this->cache->getGetCount();
    }
}
