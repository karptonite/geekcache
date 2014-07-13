<?php
namespace GeekCache\Cache;

/**
 * A memoized cache should not be long-lived. It does not respect TTL,
 * and can go out of sync if the primary is changed in another process.
 *
 * It's intended use is memoizing data retrieved over the course of a single
 * HTTP request.
 */
class MemoizedCache extends CacheDecorator
{
    private $memocache;

    public function __construct(Cache $primaryCache, Cache $memocache)
    {
        parent::__construct($primaryCache);
        $this->memocache = $memocache;
    }

    public function get($key, callable $regenerator = null, $ttl = null)
    {
        $result = $this->memocache->get($key);
        return $result !== false ? $result : $this->getAndMemoize($key, $regenerator, $ttl);
    }

    public function put($key, $value, $ttl = null)
    {
        parent::put($key, $value, $ttl);
        $this->memocache->put($key, $value);
    }

    private function getAndMemoize($key, callable $regenerator = null, $ttl = null)
    {
        $value = parent::get($key, $regenerator, $ttl);
        $this->memoize($key, $value);
        return $value;
    }
    
    protected function memoize($key, $value)
    {
        $this->memocache->put($key, $value);
    }

    public function delete($key)
    {
        parent::delete($key);
        $this->memocache->delete($key);
    }

    public function clear()
    {
        parent::clear();
        return $this->memocache->clear();
    }
}
