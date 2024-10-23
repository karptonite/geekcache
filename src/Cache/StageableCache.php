<?php

namespace GeekCache\Cache;

use GeekCache\Cache\MultiGetCache;

class StageableCache extends AbstractBaseCache implements Cache
{
    private MultiGetCache $cache;
    private int $getCount = 0;
    private StagingCache $stagingCache;

    public function __construct(MultiGetCache $cache)
    {
        $this->cache = $cache;
        $this->stagingCache = new StagingCache();
    }

    public function stage(string $key): void
    {
        $this->stagingCache->stage($key);
    }
    
    public function unstage(string $key): void
    {
        $this->stagingCache->unstage($key);
    }
    
    public function get($key, callable $regenerator = null, $ttl = 0)
    {
        // if we have a pending result, use that
        if ($this->stagingCache->resultIsStaged($key)) {
            $result = $this->stagingCache->readResult($key);
            // if we do not have a pending result, go to the database
        } else {
            if ($this->stagingCache->anyRequestsStaged()) {
                $result = $this->getWithStaged($key);
            } else {
                $result = $this->cache->get($key);
            }
            $this->getCount += 1;
        }

        return $result !== false ? $result : $this->regenerate($key, $regenerator, $ttl);
    }

    private function getWithStaged($key)
    {
        $results = $this->cache->getMulti(
            array_unique(array_merge(
                [$key],
                $this->stagingCache->getStagedRequests()
            ))
        );
        
        $result = array_key_first($results) === $key ? $results[$key] : false;
        
        $this->stagingCache->stageResults($key, $results);
        return $result;
    }


    public function put($key, $value, $ttl = 0)
    {
        return $this->cache->put($key, $value, (int)$ttl);
    }

    public function delete($key)
    {
        return $this->cache->delete($key);
    }

    public function clear()
    {
        return $this->cache->clear();
    }

    // functions below here are used only as test spys
    public function getGetCount(): int
    {
        return $this->getCount;
    }
    public function getStagedRequestsCount(): int
    {
        return $this->stagingCache->getStagedRequestsCount();
    }
    public function getStagedResultsCount(): int
    {
        return $this->stagingCache->getStagedResultsCount();
    }
}
