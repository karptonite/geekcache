<?php
namespace GeekCache\Cache;

class MemoizedIncrementableCache extends MemoizedCache implements IncrementableCache
{
    private $incrementablecache;

    public function __construct(IncrementableCache $incrementablecache, Cache $memocache)
    {
        parent::__construct($incrementablecache, $memocache);
        $this->incrementablecache = $incrementablecache;
    }

    public function increment($key, $value = 1)
    {
        $this->incrementablecache->increment($key, $value);
        return $this->getAndMemoize($key);
    }
}
