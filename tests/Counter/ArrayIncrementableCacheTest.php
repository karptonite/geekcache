<?php
class ArrayIncrementableCacheTest extends BaseIncrementableCacheTest
{
    public function setUp()
    {
        parent::setUp();
        $this->cache = new GeekCache\Cache\IncrementableArrayCache;
    }
}
