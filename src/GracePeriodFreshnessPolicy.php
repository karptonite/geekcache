<?php
namespace Geek\Cache;

class GracePeriodFreshnessPolicy extends AbstractFreshnessPolicy
{
	private $gracePeriod;
	private $expiry;

	public function __construct( $gracePeriod = null )
	{
		$this->gracePeriod = $gracePeriod;
	}

	public function computeTtl( $ttl )
	{
		return $ttl && $this->gracePeriod ? $ttl + $this->gracePeriod : null;
	}

	protected function isFresh( $metadata )
	{
		return isset( $metadata['expiry'] ) && ( !$metadata['expiry'] || $metadata['expiry'] > microtime( true ) );
	}

	protected function createMetadata( $ttl )
	{
		$expiry = $ttl ? microtime( true ) + $ttl : 0;
		return array( 'expiry' =>  $expiry );
	}
}	
