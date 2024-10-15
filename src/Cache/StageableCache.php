<?php

namespace GeekCache\Cache;

class StageableCache extends AbstractBaseCache implements Cache
{
    private $cache;
    private int $getCount = 0;
    private array $stagedRequests = [];
    private array $stagedResults = [];

    public function __construct(\Memcached $cache)
    {
        $this->cache = $cache;
    }

    public function stage(string $key): void
    {
        $this->stagedRequests[$key] = ($this->stagedRequests[$key] ?? null) ? $this->stagedRequests[$key] + 1 : 1;
    }

    public function get($key, callable $regenerator = null, $ttl = 0)
    {
        // if we have a pending result, use that
        if (array_key_exists($key, $this->stagedResults)) {
            $result = $this->stagedResults[$key]['value'];
            $this->stagedResults[$key]['remainingReads']--;
            if (!$this->stagedResults[$key]['remainingReads']) {
                unset($this->stagedResults[$key]);
            }
            // if we do not have a pending result, go to the database
        } else {
            if (count($this->stagedRequests)) {
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
        $results = $this->multiGet(array_unique(array_merge([$key], array_keys($this->stagedRequests))));
        $result = false;
        if (array_key_first($results) === $key) {
            $result = $results[$key];
        }

        // if the result we are getting was also staged, we have to handle it
        if (array_key_exists($key, $this->stagedRequests)) {
            $this->stagedRequests[$key]--;
            if ($this->stagedRequests[$key] <= 0) {
                unset($this->stagedRequests[$key]);
            }
        }

        foreach ($this->stagedRequests as $key => $stageCount) {
            $this->stagedResults[$key] = [
              'value' => $results[$key] ?? false,
                'remainingReads' => $stageCount
            ];
        }
        $this->stagedRequests = [];
        return $result;
    }

    private function multiGet(array $keys)
    {
        return $this->cache->getMulti($keys);
    }

    public function put($key, $value, $ttl = 0)
    {
        return $this->cache->set($key, $value, (int)$ttl);
    }

    public function delete($key)
    {
        return $this->cache->delete($key);
    }

    public function clear()
    {
        return $this->cache->flush();
    }

    // functions below here are used only as test spys
    public function getGetCount(): int
    {
        return $this->getCount;
    }
    public function getStagedRequestsCount(): int
    {
        return count($this->stagedRequests);
    }
    public function getStagedResultsCount(): int
    {
        return count($this->stagedResults);
    }
}
