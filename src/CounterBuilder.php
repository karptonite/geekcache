<?php
namespace GeekCache\Cache;
class CounterBuilder
{
    private $cache;
    private $memocache;

    public function __construct( IncrementableCache $cache, IncrementableCache $memocache, array $stack = null )
    {
        $this->cache = $cache;
        $this->memocache = $memocache;
        $this->stack = $stack ?: array( function() use( $cache ){ return $cache; } );
    }
    
    public function make( $key, $ttl = null )
    {
        $stack = $this->stack;
        $cache = $this->cache;

        while( $factory = array_shift( $stack ) )
            $cache = $factory( $cache );

        return new NormalCounter( $cache, $key, $ttl );
    }

    private function addToStack( $factory )
    {
        $stack = $this->stack;
        $stack[] = $factory;
        return new self( $this->cache, $this->memocache, $stack );
    }

    public function memoize()
    {
        $memocache = $this->memocache;

        $factory = function( $cache ) use ( $memocache ){
            return new MemoizedIncrementableCache( $cache, $memocache );
        };

        return $this->addToStack( $factory );
    }
}

