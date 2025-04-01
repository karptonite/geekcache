<?php
class CacheServiceProviderIlluminateTest extends CacheServiceProviderTestAbstract
{
    public function getContainer()
    {
        return new Illuminate\Container\Container;
    }
}
