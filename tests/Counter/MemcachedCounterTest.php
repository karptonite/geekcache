<?php
use Mockery as m;

class MemcachedCounterTest extends PHPUnit_Framework_TestCase
{
    const KEY   = 'thekey';
    private $cache;

    public function setUp()
    {
        parent::setUp();
        $memcached = new Memcached();
        $memcached->addServer('localhost', 11211);
        $memcached->flush();
        $this->cache = new GeekCache\Cache\IncrementableMemcachedCache($memcached);
    }

    /**
     * @group slowTests
     */
    public function testCounterExpires()
    {
        $counter = new GeekCache\Counter\NormalCounter($this->cache, self::KEY, 1);
        $counter->increment(1);
        $this->assertEquals(1, $counter->get());

        usleep(2100000);
        $this->assertFalse($counter->get());
    }
}
