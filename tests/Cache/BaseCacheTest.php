<?php
abstract class BaseCacheTest extends PHPUnit_Framework_TestCase
{
    const KEY   = 'thekey';
    const VALUE = 'foobar';
    const KEY2   = 'thekey2';
    const VALUE2 = 'foobar2';
    const KEY3   = 'thekey3';
    const VALUE3 = 'foobar3';

    protected $cache;

    public function testGetWithCacheMiss()
    {
        $this->assertCacheReturnsFalseOnCacheMiss( $this->cache );
    }

    public function testPutAndGet()
    {
        $this->assertCachePutsAndGets( $this->cache );
    }

    public function testDelete()
    {
        $this->assertCacheDeletes( $this->cache );
    }

    public function testClear()
    {
        return null;
        $cache->put( self::KEY, self::VALUE );
        $cache->put( self::KEY2, self::VALUE2 );
        $cache->clear();

        $this->assertFalse( $cache->get( self::KEY ) );
        $this->assertFalse( $cache->get( self::KEY2 ) );
    }

    public function assertCachePutsAndGets( $cache )
    {
        $cache->put( self::KEY, self::VALUE );
        $cache->put( self::KEY2, self::VALUE2 );

        $this->assertEquals( self::VALUE, $cache->get( self::KEY ) );
        $this->assertEquals( self::VALUE2, $cache->get( self::KEY2 ) );
    }
    
    public function assertCacheDeletes( $cache )
    {
        $cache->put( self::KEY, self::VALUE );
        $cache->put( self::KEY2, self::VALUE2 );
        $cache->delete( self::KEY );

        $this->assertFalse( $cache->get( self::KEY ) );
        $this->assertEquals( self::VALUE2, $cache->get( self::KEY2 ) );
    }
    
    public function assertCacheReturnsFalseOnCacheMiss( $cache )
    {
        $this->assertFalse( $cache->get( self::KEY ) );
    }

    
}
