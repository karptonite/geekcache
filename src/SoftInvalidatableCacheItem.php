<?php
namespace GeekCache\Cache;

class SoftInvalidatablecacheItem extends NormalCacheItem
{
    private $softcache;

    public function __construct( SoftInvalidatable $cache, $key, $ttl = null )
    {
        parent::__construct( $cache, $key, $ttl );
        $this->softcache = $cache;
    }
    
    public function getStale()
    {
        return $this->softcache->getStale( $this->key );
    }
}

