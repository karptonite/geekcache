<?php

class CacheServiceProviderPimpleTest extends CacheServiceProviderTest
{
    public function getContainer()
    {
        return new Pimple;
    }
    
}
 
