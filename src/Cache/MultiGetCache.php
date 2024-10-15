<?php

namespace GeekCache\Cache;

interface MultiGetCache
{
    public function getMulti(array $keys): array;
    public function get($key);
    public function put($key, $value, $ttl = 0);
    public function delete($key);
    public function clear();
}
