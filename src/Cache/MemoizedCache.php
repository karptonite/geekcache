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

    public function getMulti(array $keys)
    {
        $results = $this->memocache->getMulti($keys);
        $missingKeys = [];
        foreach( $results as $key => $result) {
            if($result === false) {
                $missingKeys[] = $key;
            }
        }
        
        $primaryResults = $this->getMultiAndMemoize($missingKeys);
        // FIXME test that this preserves the order
        foreach($primaryResults as $key => $value) {
            $results[$key] = $value;
        }
        return $results;
    }
    
    public function get($key, callable $regenerator = null, $ttl = null)
    {
        $result = $this->memocache->get($key);
        return $result !== false ? $result : $this->getAndMemoize($key, $regenerator, $ttl);
    }

    public function put($key, $value, $ttl = null)
    {
        $this->memocache->put($key, $value);
        return parent::put($key, $value, $ttl);
    }
    
    private function getMultiAndMemoize($keys)
    {
        $values = parent::getMulti($keys);
        foreach($values as $key => $value)
        {
            $this->memoize($key, $value);
        }
        return $values;
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
