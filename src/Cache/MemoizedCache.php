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

    public function __construct(Cache $primaryCache, CheckableCache $memocache)
    {
        parent::__construct($primaryCache);
        $this->memocache = $memocache;
    }
    
    public function stage($key, ?string $skipIfStaged = null):void
    {
        if (!$this->memocache->has($key)) {
            parent::stage($key, $skipIfStaged);
        }
    }

    public function get($key, ?callable $regenerator = null, $ttl = 0)
    {
        $result = $this->memocache->get($key);
        if ($result !== false) {
            // decrementing the staged result count, if any to indicate it was read from the memoized cache
            // FIXME we could probable just clear the staged results here,
            // but for other purposes, we need to be able to decrement,
            // and decrement works as well as clearing for our purposes.
            parent::decrementStagedCount($key);
            return $result;
        }
        return $this->getAndMemoize($key, $regenerator, $ttl);
    }

    public function put($key, $value, $ttl = 0)
    {
        $this->memocache->put($key, $value);
        return parent::put($key, $value, $ttl);
    }

    private function getAndMemoize($key, ?callable $regenerator = null, $ttl = 0)
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
