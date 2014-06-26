<?php
namespace GeekCache\Cache;

interface CacheItem
{
    public function get();
    public function put( $value );
    public function delete();
}
