<?php
namespace Geek\Cache;

class TaggedCache extends SoftInvalidatableCacheDecorator
{
	private $tagset;

	public function __construct( Cache $cache, TagSet $tagset, SoftInvalidatable $softCache = null )
	{
		parent::__construct( $cache, $softCache );
		$this->tagset = $tagset;
	}

	protected function resultIsCurrent()
	{
		$metadata = $this->getMetadata();
		return isset( $metadata['signature'] ) && $metadata['signature'] == $this->tagset->getSignature();
	}

	protected function createMetadata()
	{
		return array( 'signature' => $this->tagset->getSignature() );
	}
}
