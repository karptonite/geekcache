<?php
class NamespacedIncrementableCacheTest extends BaseIncrementableCacheTest
{
    private $primarycache;

    const CACHE_NAMESPACE = 'ns';

    public function setUp()
    {
        parent::setUp();
        $this->primarycache  = new GeekCache\Cache\IncrementableArrayCache;
        $this->cache = new GeekCache\Cache\IncrementableNamespacedCache($this->primarycache, self::CACHE_NAMESPACE);
    }
}
