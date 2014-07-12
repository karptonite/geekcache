<?php
namespace GeekCache\Cache;

class MemcacheCache extends AbstractBaseCache implements Cache
{
    private $cache;

    public function __construct(\Memcache $cache)
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
        $compressed = is_int($value) ? null : MEMCACHE_COMPRESSED;
        return $this->cache->set($key, $value, $compressed, (int)$ttl);
    }

    public function delete($key)
    {
        //passing an explicit 0 for timeout because of this issue:
        //http://stackoverflow.com/questions/4745345/how-do-i-stop-phpmemcachedelete-from-producing-a-client-error
        return $this->cache->delete($key, 0);
    }

    public function clear()
    {
        return $this->cache->flush();
    }
}
