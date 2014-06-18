<?php
namespace Geek\Cache;

class GracePeriodValidator
{
	private $gracePeriod;
	private $expiry;

	public function __construct( $gracePeriod = null )
	{
		$this->gracePeriod = $gracePeriod;
	}

	public function packValue( $value, $ttl = null )
	{
		return new CacheData( $value, $this->createMetadata( $ttl ) );
	}

	public function unpackValue( $result )
	{
		if( !( $result instanceof CacheData ) )
			return false;

		return $result->getValue();
	}

	public function computeTtl( $ttl )
	{
		return $ttl && $this->gracePeriod ? $ttl + $this->gracePeriod : null;
	}

	public function resultIsCurrent( $result )
	{
		if( !( $result instanceof CacheData ) )
			return false;

		$metadata = $result->getMetadata();
		return isset( $metadata['expiry'] ) && ( !$metadata['expiry'] || $metadata['expiry'] > microtime( true ) );
	}

	protected function createMetadata( $ttl )
	{
		$expiry = $ttl ? microtime( true ) + $ttl : 0;
		return array( 'expiry' =>  $expiry );
	}
}	
