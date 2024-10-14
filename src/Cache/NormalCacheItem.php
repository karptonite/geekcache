<?php

namespace GeekCache\Cache;

class NormalCacheItem implements CacheItem
{
    private $cache;
    protected $key;
    protected $ttl;

    public function __construct(Cache $cache, $key, $ttl = 0)
    {
        $this->cache = $cache;
        $this->key = $key;
        $this->ttl = $ttl;
    }
    
    public function stage()
    {
        $this->cache->stage($this->key);
    }

    public function get($regenerator = null)
    {
        return $this->cache->get($this->key, $regenerator, $this->ttl);
    }

    public function put($value)
    {
        return $this->cache->put($this->key, $value, $this->ttl);
    }

    public function delete()
    {
        return $this->cache->delete($this->key);
    }
}
