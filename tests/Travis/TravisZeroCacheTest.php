
<?php
class TravisZeroCacheTest extends PHPUnit_Framework_TestCase
{
	private $cache;

	const KEY = "foo";

	public function setUp()
	{
		parent::setUp();
		$this->memcache = new Memcache();
		$this->memcache->connect('localhost', 11211);
		$this->memcache->flush();
	}

	public function testSetAndGetZero()
	{
		$this->memcache->set(self::KEY, 0);
		$this->assertSame(0, $this->memcache->get(self::KEY));
	}

	public function testSetAndGetOne()
	{
		$this->memcache->set(self::KEY, 1);
		$this->assertSame(1, $this->memcache->get(self::KEY));
	}

}
