<?php

namespace GeekCache\Cache;

interface CheckableCache extends Cache
{
    public function has($key): bool;
}
