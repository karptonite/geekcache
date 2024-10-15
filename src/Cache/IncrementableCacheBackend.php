<?php

namespace GeekCache\Cache;

interface IncrementableCacheBackend
{
    public function add($key, $value, $ttl = 0);
    public function increment($key, $value):false|int;
    public function decrement($key, $value):false|int;
}
