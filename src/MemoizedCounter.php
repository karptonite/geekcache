<?php
namespace Geek\Cache;

class MemoizedCounter extends MemoizedCache implements Counter
{
	private $primarycounter;

	public function __construct( Counter $primarycounter, Cache $memocache )
	{
		parent::__construct( $primarycounter, $memocache );
		$this->primarycounter = $primarycounter;
	}

	public function increment( $key, $value = 1 )
	{
		$this->primarycounter->increment( $key, $value );
		return $this->getAndMemoize( $key );
	}
}
