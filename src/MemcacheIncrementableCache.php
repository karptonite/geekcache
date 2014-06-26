<?php
namespace GeekCache\Cache;

class MemcacheIncrementableCache extends MemcacheCache implements IncrementableCache
{
    private $cache;

    public function __construct(\Memcache $cache)
    {
        parent::__construct($cache);
        $this->cache = $cache;
    }

    public function put($key, $value, $ttl = null)
    {
        return $this->cache->set($key, $value, null, (int)$ttl);
    }

    public function increment($key, $value = 1)
    {
        if ($value < 0) {
            return $this->decrement($key, abs($value));
        }

        $this->cache->add($key, 0);
        return $this->cache->increment($key, $value);
    }

    private function decrement($key, $value)
    {
        return $this->cache->decrement($key, $value);
    }
}
