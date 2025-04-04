<?php

namespace GeekCache\Cache;

class CacheBuilder
{
    private $cache;
    private $memocache;
    private $tagsetfactory;
    private $stack;

    public function __construct(Cache $cache, Cache $memocache, \GeekCache\Tag\TagSetFactory $tagsetfactory, ?array $stack = null)
    {
        $this->cache = $cache;
        $this->memocache = $memocache;
        $this->tagsetfactory = $tagsetfactory;
        $this->stack = $stack ?: array(function () use ($cache) {
            return $cache;
        });
    }

    public function make($key, $ttl = 0)
    {
        $stack = $this->stack;
        $cache = $this->cache;

        while ($factory = array_shift($stack)) {
            $cache = $factory($cache);
        }

        return new NormalCacheItem($cache, $key, $ttl);
    }

    private function addToStack(callable $factory)
    {
        $stack = $this->stack;
        $stack[] = $factory;
        return new self($this->cache, $this->memocache, $this->tagsetfactory, $stack);
    }

    public function memoize()
    {
        $memocache = $this->memocache;

        $factory = function ($cache) use ($memocache) {
            return new MemoizedCache($cache, $memocache);
        };

        return $this->addToStack($factory);
    }

    private function getSoftInvalidatableFactory($policy)
    {
        return function ($cache) use ($policy) {
            return new SoftInvalidatableCache($cache, $policy);
        };
    }

    public function addTags($names)
    {
        $tagsetfactory = $this->tagsetfactory;
        $tagset        = $tagsetfactory->makeTagSet(is_array($names) ? $names : func_get_args());
        $policy        = new TaggedFreshnessPolicy($tagset);
        $factory       = $this->getSoftInvalidatableFactory($policy);
        return $this->addToStack($factory);
    }

    public function addGracePeriod($gracePeriod = null)
    {
        $policy = new GracePeriodFreshnessPolicy($gracePeriod);
        $factory = $this->getSoftInvalidatableFactory($policy);
        return $this->addToStack($factory);
    }
}
