<?php
namespace Geek\Cache;

class SoftExpiringCache extends SoftInvalidatableCacheDecorator
{
	private $expiry;

	public function __construct( Cache $cache, $softTtl = null, SoftInvalidatable $softCache = null )
	{
		$this->expiry = $softTtl ? microtime( true ) + $softTtl : 0;
		parent::__construct( $cache, $softCache );
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
