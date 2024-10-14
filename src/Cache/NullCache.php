<?php

namespace GeekCache\Cache;

class NullCache implements Cache, IncrementableCache
{
    public function get($key, callable $regenerator = null, $ttl = 0)
    {
        return false;
    }

    public function put($key, $value, $ttl = 0)
    {
        return null;
    }

    public function delete($key)
    {
        return null;
    }

    public function clear()
    {
        return true;
    }

    public function increment($key, $value = 1, $ttl = 0)
    {
        return null;
    }

    public function stage(string  $key): void
    {
    }
}
