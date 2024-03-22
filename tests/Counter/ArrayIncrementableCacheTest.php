<?php
class ArrayIncrementableCacheTest extends BaseIncrementableCacheTest
{
    public function setUp(): void
    {
        parent::setUp();
        $this->cache = new GeekCache\Cache\IncrementableArrayCache;
    }
}
