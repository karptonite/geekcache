<?php
namespace GeekCache\Cache;

class NormalCounter extends NormalCacheItem implements Counter
{
    private $incrementablecache;

    public function __construct( IncrementableCache $incrementablecache, $key, $ttl = null )
    {
        parent::__construct( $incrementablecache, $key, $ttl );
        $this->incrementablecache = $incrementablecache;
    }

    public function increment( $value = 1 )
    {
        return $this->incrementablecache->increment( $this->key, $value );
    }
}

