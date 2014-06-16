<?php
namespace Geek\Cache;

class SoftExpiringCache extends SoftInvalidatableCacheDecorator
{
	private $ttl;
	private $hardttl;

	public function __construct( Cache $cache, $hardttl = null, SoftInvalidatable $softCache = null )
	{
		$this->hardttl = $hardttl;
		parent::__construct( $cache, $softCache );
	}

	public function put( $key, $value, $ttl = null )
	{
		$this->ttl = $ttl;
		parent::put( $key, $value, $this->hardttl );
	}

	protected function resultIsCurrent()
	{
		$metadata = $this->getMetadata();
		return isset( $metadata['ttl'] ) && ( !$metadata['ttl'] || $metadata['ttl'] > microtime( true ) );
	}

	protected function createMetadata()
	{
		$ttl = $this->ttl ? microtime( true ) + $this->ttl : 0;
		return array( 'ttl' => $ttl );
	}
}	
