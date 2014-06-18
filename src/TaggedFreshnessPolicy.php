<?php
namespace Geek\Cache;

class TaggedFreshnessPolicy extends AbstractFreshnessPolicy
{
	private $tagset;

	public function __construct( TagSet $tagset )
	{
		$this->tagset = $tagset;
	}

	public function computeTtl( $ttl )
	{
		return $ttl;
	}
	
	protected function isFresh( $metadata )
	{
		return isset( $metadata['signature'] ) && $metadata['signature'] == $this->tagset->getSignature();
	}

	protected function createMetadata( $ttl )
	{
		return array( 'signature' => $this->tagset->getSignature() );
	}
}
