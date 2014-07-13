<?php
namespace GeekCache\Counter;

interface Counter extends \GeekCache\Cache\CacheItem
{
    public function increment($value = 1);
}
