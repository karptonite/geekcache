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

    public function __construct( Cache $primaryCache, Cache $memocache )
    {
        parent::__construct( $primaryCache );
        $this->memocache = $memocache;
    }

    public function get( $key )
    {
        return $this->memocache->get( $key )?: $this->getAndMemoize( $key );
    }

    public function put( $key, $value, $ttl = null )
    {
        parent::put( $key, $value, $ttl );
        $this->memocache->put( $key, $value );
    }
    
    protected function getAndMemoize( $key )
    {
        $value = parent::get( $key );
        $this->memocache->put( $key, $value );
        return $value;
    }

    public function delete( $key )
    {
        parent::delete( $key );
        $this->memocache->delete( $key );
    }

    public function clear()
    {
        parent::clear();
        return $this->memocache->clear();
    }
}
