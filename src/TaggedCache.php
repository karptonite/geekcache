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
		return is_array( $this->result ) && $this->result['validator'] == $this->tagset->getHash();
	}

	protected function getValidator()
	{
		return $this->tagset->getHash();
	}
}
