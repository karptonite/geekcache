<?php

namespace GeekCache\Cache;

interface CacheItem
{
    public function get($regenerator = null);
    public function put($value);
    public function delete();
}
