<?php
use Mockery as m;

class TagFactoryTest extends PHPUnit_Framework_TestCase
{
	const TAG = 'TheTag';

	public function setUp()
	{
		$this->cache = new Geek\Cache\ArrayCache();
		$this->factory = new Geek\Cache\TagFactory( $this->cache );
	}

	public function testTagFactoryReturnsTag()
	{
		$tag = $this->factory->makeTag( static::TAG );
	}
	
	public function testTagFactoryReturnsTagWithCorrectProperties()
	{
		$cache = m::mock( 'Geek\Cache\Cache' );
		$cache->shouldReceive( 'get' )
			->with( static::TAG )
			->once()
			->andReturn( false );

		$cache->shouldReceive( 'put' )
			->with( static::TAG, m::any() )
			->once();
		
		$factory = new Geek\Cache\TagFactory( $cache );
		
		$tag = $factory->makeTag( static::TAG );
		$tag->getVersion();
	}
}
	
