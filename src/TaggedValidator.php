<?php
namespace Geek\Cache;

class TaggedValidator
{
	private $tagset;

	public function __construct( TagSet $tagset )
	{
		$this->tagset = $tagset;
	}

	public function packValue( $value, $ttl )
	{
		return new CacheData( $value, $this->createMetadata() );
	}

	public function unpackValue( $result )
	{
		if( !( $result instanceof CacheData ) )
			return false;

		return $result->getValue();
	}

	public function computeTtl( $ttl )
	{
		return $ttl;
	}
	

	public function resultIsCurrent( $result )
	{
		if( !( $result instanceof CacheData ) )
			return false;

		$metadata = $result->getMetadata();
		return isset( $metadata['signature'] ) && $metadata['signature'] == $this->tagset->getSignature();
	}

	private function createMetadata()
	{
		return array( 'signature' => $this->tagset->getSignature() );
	}
}
