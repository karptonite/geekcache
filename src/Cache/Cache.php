<?php

namespace GeekCache\Cache;

interface Cache
{
    public function get($key, callable $regenerator = null, $ttl = 0);
    public function put($key, $value, $ttl = 0);
    public function delete($key);
    public function clear();
}
