<?php
use Mockery as m;

class CounterTest extends PHPUnit\Framework\TestCase
{
    const KEY   = 'thekey';
    const KEY2   = 'thekey2';
    const VALUE = 10;
    const VALUE2 = 5;
    const TTL = 20;
    private $cache;
    private $counter;
    private $counter2;

    public function setUp(): void
    {
        parent::setUp();
        $this->cache = new GeekCache\Cache\IncrementableArrayCache;
        $this->counter = new GeekCache\Counter\NormalCounter($this->cache, self::KEY, self::TTL);
        $this->counter2 = new GeekCache\Counter\NormalCounter($this->cache, self::KEY2, self::TTL);
    }

    public function tearDown(): void
    {
        m::close();
        parent::tearDown();
    }
    public function assertImplementsInterface()
    {
        $this->assertInstanceOf('Geek\Counter\Counter', $this->counter);
    }


    public function testcounterPutsAndGets()
    {
        $this->counter->put(self::VALUE);
        $this->counter2->put(self::VALUE2);

        $this->assertEquals(self::VALUE, $this->cache->get(self::KEY));
        $this->assertEquals(self::VALUE2, $this->cache->get(self::KEY2));
        $this->assertEquals(self::VALUE, $this->counter->get());
        $this->assertEquals(self::VALUE2, $this->counter2->get());
    }

    public function testCounterIncrements()
    {
        $this->counter->put(self::VALUE);
        $result = $this->counter->increment();
        $this->assertEquals(self::VALUE + 1, $this->counter->get());
        $this->assertEquals(self::VALUE + 1, $result);
    }

    public function testCounterIncrementsByValue()
    {
        $this->counter->put(self::VALUE);
        $result = $this->counter->increment(2);
        $this->assertEquals(self::VALUE + 2, $this->counter->get());
        $this->assertEquals(self::VALUE + 2, $result);
    }
}
