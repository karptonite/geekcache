<?php
namespace GeekCache\Cache;

class GracePeriodFreshnessPolicy extends AbstractFreshnessPolicy
{
    private $gracePeriod;

    public function __construct( $gracePeriod = null )
    {
        $this->gracePeriod = $gracePeriod;
    }

    public function computeTtl( $ttl )
    {
        //if there is no $ttl, the cache lives forever, period.
        //if the graceperiod is zero, we still live forever, because we can always get the stale data.
        return $ttl && $this->gracePeriod ? $ttl + $this->gracePeriod : null;
    }

    protected function isFresh( $freshnessData )
    {
        return isset( $freshnessData['expiry'] ) && ( !$freshnessData['expiry'] || $freshnessData['expiry'] > microtime( true ) );
    }

    protected function createFreshnessData( $ttl )
    {
        $expiry = $ttl ? microtime( true ) + $ttl : 0;
        return array( 'expiry' =>  $expiry );
    }
}   
