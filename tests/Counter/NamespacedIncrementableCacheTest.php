<?php
class NamespacedIncrementableCacheTest extends BaseIncrementableCacheTest
{
    private $primarycache;

    const CACHE_NAMESPACE = 'ns';

    public function setUp()
    {
        parent::setUp();
        $this->primarycache  = new GeekCache\Cache\ArrayIncrementableCache;
        $this->cache = new GeekCache\Cache\NamespacedIncrementableCache($this->primarycache, self::CACHE_NAMESPACE);
    }
}
