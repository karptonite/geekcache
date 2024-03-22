<?php
use Mockery as m;

class MemcachedCounterTest extends PHPUnit\Framework\TestCase
{
    const KEY   = 'thekey';
    private $cache;

    public function setUp(): void
    {
        parent::setUp();
        $memcached = new Memcached();
        $memcached->addServer('localhost', 11211);
        $memcached->flush();
        $this->cache = new GeekCache\Cache\IncrementableMemcachedCache($memcached);
    }

    public function tearDown(): void
    {
        m::close();
        parent::tearDown();
    }
    /**
     * @group slowTests
     */
    public function testCounterExpires()
    {
        $counter = new GeekCache\Counter\NormalCounter($this->cache, self::KEY, 1);
        $result = $counter->increment(1);
        $this->assertEquals(1, $result);
        $this->assertEquals(1, $counter->get());

        usleep(2100000);
        $this->assertFalse($counter->get());
    }

    /**
     * @group slowTests
     */
    public function testCounterExpiresWithBuilder()
    {
        $builder = new GeekCache\Counter\CounterBuilder($this->cache, $this->cache);
        $counter = $builder->make(self::KEY, 1);
        $result = $counter->increment(1);
        $this->assertEquals(1, $result);
        $this->assertEquals(1, $counter->get());

        usleep(2100000);
        $this->assertFalse($counter->get());
    }

    /**
     * @group slowTests
     */
    public function testCounterExpiresOnIncrement()
    {
        $counter = new GeekCache\Counter\NormalCounter($this->cache, self::KEY, 1);
        $counter->increment(1);
        $this->assertEquals(1, $counter->get());
        $counter->increment(1);
        $this->assertEquals(2, $counter->get());

        usleep(2100000);
        $this->assertEquals(1, $counter->increment(1));
    }
}
