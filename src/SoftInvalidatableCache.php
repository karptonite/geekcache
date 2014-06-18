<?php
namespace Geek\Cache;

class SoftInvalidatableCache extends CacheDecorator implements SoftInvalidatable
{
	private $softCache;
	private $validator;

	public function __construct( Cache $cache, $validator, SoftInvalidatable $softCache = null )
	{
		parent::__construct( $cache );
		$this->softCache = $softCache;
		$this->validator = $validator;
	}

	public function put( $key, $value, $ttl = null )
	{
		parent::put( $key, $this->validator->packValue( $value, $ttl ), $this->validator->computeTtl( $ttl ) );
	}
	
	public function get( $key )
	{
		$result = parent::get( $key );
		return $this->validator->resultIsCurrent( $result ) ? $this->validator->unpackValue( $result ) : false;
	}
	
	public function getStale( $key )
	{
		$result = $this->softCache ? $this->softCache->getStale( $key ) : parent::get( $key );
		return $this->validator->unpackValue( $result );
	}
}
