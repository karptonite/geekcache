<?php
namespace GeekCache\Cache;

class NamespacedCounter extends NamespacedCache implements Counter 
{
	private $counter;

	public function __construct( Counter $counter, $namespace )
	{
		parent::__construct( $counter, $namespace );
		$this->counter = $counter;
	}
	
	public function increment( $key, $value = 1 )
	{
		return $this->counter->increment( $this->reviseKey( $key ), $value );
	}
}


