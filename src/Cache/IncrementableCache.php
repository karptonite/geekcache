<?php

namespace GeekCache\Cache;

interface IncrementableCache extends Cache
{
    public function increment($key, $value = 1, $ttl = 0);
}
