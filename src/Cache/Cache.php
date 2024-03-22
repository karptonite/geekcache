<?php

namespace GeekCache\Cache;

interface Cache
{
    public function get($key, callable $regenerator = null, $ttl = null);
    public function put($key, $value, $ttl = null);
    public function delete($key);
    public function clear();
}
