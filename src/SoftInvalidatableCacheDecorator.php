<?php
namespace Geek\Cache;

abstract class SoftInvalidatableCacheDecorator extends CacheDecorator implements SoftInvalidatable
{
	private $softCache;
	private $result;

	public function __construct( Cache $cache, SoftInvalidatable $softCache = null )
	{
		parent::__construct( $cache );
		$this->softCache = $softCache;
	}

	public function put( $key, $value, $ttl = null )
	{
		$newvalue = new CacheData( $value, $this->createMetadata() );
		parent::put( $key, $newvalue, $ttl );
	}
	
	public function get( $key )
	{
		$this->result = parent::get( $key );
		return $this->resultIsCurrent() ? $this->getValue() : false;
	}
	
	public function getStale( $key )
	{
		$this->result = $this->softCache ? $this->softCache->getStale( $key ) : parent::get( $key );
		return $this->getValue();
	}

	protected function getMetadata()
	{
		return ( $this->result instanceof CacheData ) ? $this->result->getMetadata() : null;
	}
	
	private function getValue()
	{
		return ( $this->result instanceof CacheData ) ? $this->result->getValue() : null;
	}
}
