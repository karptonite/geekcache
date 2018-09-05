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
        $this->assertCacheReturnsFalseOnCacheMiss($this->cache);
    }

    public function testPutAndGet()
    {
        $this->assertCachePutsAndGets($this->cache);
    }

    public function testPutReturnsTrueOnSuccess()
    {
        $result = $this->cache->put( self::KEY, self::VALUE );
        $this->assertTrue( $result );
    }

    public function testPutAndGetZero()
    {
        $this->cache->put(self::KEY, 0);
        $this->assertSame(0, $this->cache->get(self::KEY));
    }

    public function testPutAndGetNull()
    {
        $this->cache->put(self::KEY, null);
        $this->assertNull($this->cache->get(self::KEY));
    }

    public function testDelete()
    {
        $this->assertCacheDeletes($this->cache);
    }

    public function testClear()
    {
        $this->cache->put(self::KEY, self::VALUE);
        $this->cache->put(self::KEY2, self::VALUE2);
        $this->cache->clear();

        $this->assertFalse($this->cache->get(self::KEY));
        $this->assertFalse($this->cache->get(self::KEY2));
    }

    public function testGetCacheCallsRegeneratorWhenCacheIsEmpty()
    {
        $called = 0;
        $regenerator = function () use (&$called) {
            $called += 1;
            return 'foo';
        };

        $this->cache->get(self::KEY, $regenerator);

        $this->assertEquals(1, $called);
    }

    public function testGetDoesNotCallRegeneratorWhenCacheIsFull()
    {
        $called = false;
        $regenerator = function () use (&$called) {
            $called = true;
        };

        $this->cache->put(self::KEY, self::VALUE);
        $this->cache->get(self::KEY, $regenerator);

        $this->assertFalse($called);
    }

    public function testCacheGetsDataFromRegenertorAndReturnsIt()
    {
        $value = self::VALUE;
        $regenerator = function () use ($value) {
            return $value;
        };

        $result = $this->cache->get(self::KEY, $regenerator);
        $this->assertEquals(self::VALUE, $result);
    }

    public function testCacheCachesRegeneratedData()
    {
        $value = self::VALUE;
        $regenerator = function () use ($value) {
            return $value;
        };

        $regenResult = $this->cache->get(self::KEY, $regenerator);
        $result = $this->cache->get(self::KEY);
        $this->assertEquals(self::VALUE, $regenResult);
        $this->assertEquals(self::VALUE, $result);
    }

    public function testCacheCachesRegeneratedNull()
    {
        $regenerator = function () {
            return null;
        };

        $regenResult = $this->cache->get(self::KEY, $regenerator);
        $result = $this->cache->get(self::KEY);
        $this->assertNull($regenResult);
        $this->assertNull($result);
    }

    public function testCacheCachesRegeneratedZero()
    {
        $regenerator = function () {
            return 0;
        };

        $regenResult = $this->cache->get(self::KEY, $regenerator);
        $result = $this->cache->get(self::KEY);
        $this->assertSame(0, $regenResult);
        $this->assertSame(0, $result);
    }

    public function assertCachePutsAndGets($cache)
    {
        $cache->put(self::KEY, self::VALUE);
        $cache->put(self::KEY2, self::VALUE2);

        $this->assertEquals(self::VALUE, $cache->get(self::KEY));
        $this->assertEquals(self::VALUE2, $cache->get(self::KEY2));
    }

    public function assertCacheDeletes($cache)
    {
        $cache->put(self::KEY, self::VALUE);
        $cache->put(self::KEY2, self::VALUE2);
        $cache->delete(self::KEY);

        $this->assertFalse($cache->get(self::KEY));
        $this->assertEquals(self::VALUE2, $cache->get(self::KEY2));
    }

    public function assertCacheReturnsFalseOnCacheMiss($cache)
    {
        $this->assertFalse($cache->get(self::KEY));
    }
}
