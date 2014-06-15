<?php
namespace Geek\Cache;

class SoftExpiringCache extends SoftInvalidatableCacheDecorator
{
	private $ttl;

	public function put( $key, $value, $ttl = null )
	{
		$this->ttl = $ttl;
		parent::put( $key, $value );
	}

	protected function resultIsCurrent()
	{
		return is_array( $this->result ) && ( !$this->result['validator'] || $this->result['validator'] > microtime( true ) );
	}

	protected function getValidator()
	{
		return $this->ttl ? microtime( true ) + $this->ttl : null;
	}
}	
