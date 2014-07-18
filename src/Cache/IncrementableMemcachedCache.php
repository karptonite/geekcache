<?php
namespace GeekCache\Cache;

class IncrementableMemcachedCache extends MemcachedCache implements IncrementableCache
{
    private $cache;

    public function __construct(\Memcached $cache)
    {
        parent::__construct($cache);
        $this->cache = $cache;
    }

    public function increment($key, $value = 1)
    {
        if ($value < 0) {
            return $this->decrement($key, abs($value));
        }

        //calling add before incrementing prevents a race condition when two processes try to increment
        //the same value http://php.net/manual/en/memcache.increment.php#90864
        $this->cache->add($key, 0);
        return $this->cache->increment($key, $value);
    }

    private function decrement($key, $value)
    {
        return $this->cache->decrement($key, $value);
    }
}
