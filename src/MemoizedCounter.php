<?php
namespace Geek\Cache;

class MemoizedCounter extends MemoizedCache implements Counter
{
	private $primarycounter;

	public function __construct( Counter $primarycounter, Counter $memocounter )
	{
		parent::__construct( $primarycounter, $memocounter );
		$this->primarycounter = $primarycounter;
	}

	public function increment( $key, $value = 1 )
	{
		$this->primarycounter->increment( $key, $value );
		return $this->getAndBuffer( $key );
	}
}
