<?php
namespace Geek\Cache;

class SoftExpiringCache extends SoftInvalidatableCacheDecorator
{
	private $gracePeriod;
	private $expiry;

	public function __construct( Cache $cache, $gracePeriod = null, SoftInvalidatable $softCache = null )
	{
		$this->gracePeriod = $gracePeriod;
		parent::__construct( $cache, $softCache );
	}

	public function put( $key, $value, $ttl = null )
	{
		$this->expiry = $ttl ? microtime( true ) + $ttl : 0;
		parent::put( $key, $value, $this->getExtendedTtl( $ttl ) );
	}

	private function getExtendedTtl( $ttl )
	{
		return $ttl && $this->gracePeriod ? $ttl + $this->gracePeriod : null;
	}
	

	protected function resultIsCurrent()
	{
		$metadata = $this->getMetadata();
		return isset( $metadata['expiry'] ) && ( !$metadata['expiry'] || $metadata['expiry'] > microtime( true ) );
	}

	protected function createMetadata()
	{
		return array( 'expiry' => $this->expiry );
	}
}	
