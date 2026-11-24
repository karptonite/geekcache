<?php

namespace GeekCache\Cache;

interface FreshnessPolicy
{
    public function packValueWithPolicy($value, $ttl);
    // called before regenerating; the policy may take its freshness data then or when packing
    public function packer($ttl): callable;
    public function unpackValue($result);
    public function resultIsFresh($result);
    public function computeTtl($ttl);
    public function getNamespace();
    // this can be called before get() ing the underlying cache, to stage any additional
    // caches
    public function stage(?string $skipIfStaged = null);
}
