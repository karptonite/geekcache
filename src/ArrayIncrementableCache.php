<?php
namespace GeekCache\Cache;

class ArrayIncrementableCache extends ArrayCache implements IncrementableCache
{
    public function increment( $key, $value = 1 )
    {
        if( $value < 0 )
            return $this->decrement( $key, abs( $value ) );

        $current = $this->get( $key );
        $newvalue = is_numeric( $current ) ? $current + $value : $value;
        $this->put( $key, $newvalue );
        return $newvalue;
    }

    private function decrement( $key, $value )
    {
        if( !$this->cacheExists( $key ) )
            return false;

        $current = $this->get( $key );
        $newvalue = max( 0, $current - $value );
        $this->put( $key, $newvalue );
        return $newvalue;
    }
}

