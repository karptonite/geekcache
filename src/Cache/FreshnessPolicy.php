<?php

namespace GeekCache\Cache;

interface FreshnessPolicy
{
    public function packValueWithPolicy($value, $ttl);
    public function unpackValue($result);
    public function resultIsFresh($result);
    public function computeTtl($ttl);
    public function getNamespace();
    // this can be called before get() ing the underlying cache, to stage any additional
    // caches
    public function stage();
}
