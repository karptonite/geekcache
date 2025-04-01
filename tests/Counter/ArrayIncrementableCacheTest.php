<?php
class ArrayIncrementableCacheTest extends BaseIncrementableCacheTestAbstract
{
    public function setUp(): void
    {
        parent::setUp();
        $this->cache = new GeekCache\Cache\IncrementableArrayCache;
    }
}
