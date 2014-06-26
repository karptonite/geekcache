<?php
namespace GeekCache\Cache;

class SoftInvalidatableCache extends CacheDecorator implements SoftInvalidatable
{
    private $softCache;
    private $policy;

    public function __construct( Cache $cache, FreshnessPolicy $policy, SoftInvalidatable $softCache = null )
    {
        parent::__construct( $cache );
        $this->softCache = $softCache;
        $this->policy = $policy;
    }

    public function put( $key, $value, $ttl = null )
    {
        parent::put( $key, $this->policy->packValueWithPolicy( $value, $ttl ), $this->policy->computeTtl( $ttl ) );
    }
    
    public function get( $key )
    {
        $result = parent::get( $key );
        return $this->policy->resultIsFresh( $result ) ? $this->policy->unpackValue( $result ) : false;
    }

    public function clear()
    {
        return parent::clear();
    }
    
    public function getStale( $key )
    {
        $result = $this->softCache ? $this->softCache->getStale( $key ) : parent::get( $key );
        return $this->policy->unpackValue( $result );
    }
}
