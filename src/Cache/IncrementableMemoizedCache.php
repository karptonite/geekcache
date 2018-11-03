<?php
namespace GeekCache\Cache;

class IncrementableMemoizedCache extends MemoizedCache implements IncrementableCache
{
    private $incrementablecache;

    public function __construct(IncrementableCache $incrementablecache, Cache $memocache)
    {
        parent::__construct($incrementablecache, $memocache);
        $this->incrementablecache = $incrementablecache;
    }

    public function increment($key, $value = 1, $ttl = 0)
    {
        $newvalue = $this->incrementablecache->increment($key, $value, $ttl);
        $this->memoize($key, $newvalue);
        return $newvalue;
    }
}
