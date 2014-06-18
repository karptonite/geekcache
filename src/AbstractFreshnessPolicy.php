<?php
namespace Geek\Cache;

abstract class AbstractFreshnessPolicy implements FreshnessPolicy
{
	public function packValueWithPolicy( $value, $ttl = null )
	{
		return new CacheData( $value, $this->createMetadata( $ttl ) );
	}

	public function unpackValue( $result )
	{
		if( !( $result instanceof CacheData ) )
			return false;

		return $result->getValue();
	}

	public function resultIsFresh( $result )
	{
		if( !( $result instanceof CacheData ) )
			return false;

		$metadata = $result->getMetadata();
		return $this->isFresh( $metadata );
	}
}	
