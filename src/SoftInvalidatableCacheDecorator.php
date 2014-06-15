<?php
namespace Geek\Cache;

abstract class SoftInvalidatableCacheDecorator extends CacheDecorator implements SoftInvalidatable
{
	private $softCache;
	protected $result;

	public function __construct( Cache $cache, SoftInvalidatable $softCache = null )
	{
		parent::__construct( $cache );
		$this->softCache = $softCache;
	}

	public function put( $key, $value, $ttl = null )
	{
		$newvalue = array( 
			'value'     => $value,
			'validator' => $this->getValidator()
		);

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
	
	private function getValue()
	{
		return is_array( $this->result ) ? $this->result['value'] : false;
	}
}
