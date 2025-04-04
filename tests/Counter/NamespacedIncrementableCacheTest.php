<?php
class NamespacedIncrementableCacheTest extends BaseIncrementableCacheTestAbstract
{
    private $primarycache;

    const CACHE_NAMESPACE = 'ns';

    public function setUp(): void
    {
        parent::setUp();
        $this->primarycache  = new GeekCache\Cache\IncrementableArrayCache;
        $this->cache = new GeekCache\Cache\IncrementableNamespacedCache($this->primarycache, self::CACHE_NAMESPACE);
    }
}
