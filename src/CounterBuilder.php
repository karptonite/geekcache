<?php
namespace GeekCache\Cache;
class CounterBuilder
{
	private $counter;
	private $memocounter;

	public function __construct( counter $counter, counter $memocounter, array $stack = null )
	{
		$this->counter = $counter;
		$this->memocounter = $memocounter;
		$this->stack = $stack ?: array( function() use( $counter ){ return $counter; } );
	}
	
	public function make()
	{
		$stack = $this->stack;
		$counter = $this->counter;

		while( $factory = array_shift( $stack ) )
			$counter = $factory( $counter );

		return $counter;
	}

	private function addToStack( $factory )
	{
		$stack = $this->stack;
		$stack[] = $factory;
		return new self( $this->counter, $this->memocounter, $stack );
	}

	public function memoize()
	{
		$memocounter = $this->memocounter;

		$factory = function( $counter ) use ( $memocounter ){
			return new MemoizedCounter( $counter, $memocounter );
		};

		return $this->addToStack( $factory );
	}
}

