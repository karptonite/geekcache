<?php
namespace Geek\Cache;

/**
 * If PHP supported it, this would be a private class for SoftInvalidatableCacheDecorator.
 * It is not intented for use elsewhere.
 */
class CacheData
{
	private $value;
	private $metadata;

	public function __construct( $value, $metadata )
	{
		$this->value    = $value;
		$this->metadata = $metadata;
	}

	public function getValue()
	{
		return $this->value;
	}

	public function getMetadata()
	{
		return $this->metadata;
	}
}
