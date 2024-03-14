<?php

namespace GeekCache\Cache;

class NamespacedCache extends CacheDecorator
{
    private $namespace;

    public function __construct( Cache $cache, $namespace )
    {
        parent::__construct( $cache );
        $this->namespace = $namespace . '_';
    }

    public function getMulti( $keys )
    {
        return array_combine($keys, parent::getMulti( array_map( function ( $key ) {
            return $this->reviseKey( $key );
        }, $keys ) ));
    }

    public function get( $key, callable $regenerator = null, $ttl = null )
    {
        return parent::get( $this->reviseKey( $key ), $regenerator, $ttl );
    }

    public function put( $key, $value, $ttl = null )
    {
        return parent::put( $this->reviseKey( $key ), $value, $ttl );
    }

    public function delete( $key )
    {
        return parent::delete( $this->reviseKey( $key ) );
    }

    public function clear()
    {
        return parent::clear();
    }

    protected function reviseKey( $key )
    {
        return substr($key, 0, strlen( $this->namespace)) === $this->namespace ? $key : $this->namespace . $key;
    }
}
