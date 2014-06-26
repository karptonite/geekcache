<?php
class CacheServiceProviderIlluminateTest extends CacheServiceProviderTest
{
    public function getContainer()
    {
        return new Illuminate\Container\Container;
    }
}
