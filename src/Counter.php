<?php
namespace GeekCache\Cache;

interface Counter extends CacheItem
{
    public function increment( $value = 1 );
}
