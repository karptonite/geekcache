<?php
namespace GeekCache\Counter;

use GeekCache\Cache;

class NormalCounter extends Cache\NormalCacheItem implements Counter
{
    private $incrementablecache;

    public function __construct(Cache\IncrementableCache $incrementablecache, $key, $ttl = null)
    {
        parent::__construct($incrementablecache, $key, $ttl);
        $this->incrementablecache = $incrementablecache;
    }

    public function increment($value = 1)
    {
        return $this->incrementablecache->increment($this->key, $value);
    }
}
