<?php
use Mockery as m;

class TagSetFactoryTest extends PHPUnit_Framework_TestCase
{
	public function testTagSetFactoryMakesTagSet()
	{
		$cache      = new Geek\Cache\ArrayCache();
		$tagFactory = new Geek\Cache\TagFactory( $cache );
		$factory    = new Geek\Cache\TagSetFactory( $tagFactory );

		$tagset = $factory->makeTagSet( array( 'foo', 'bar' ) );
		$this->assertInstanceOf( 'Geek\Cache\TagSet', $tagset );
		
		$this->assertFalse( $cache->get( 'foo' ) );
		$tagset->getSignature();
		$this->assertNotEmpty( $cache->get( 'foo' ) );
		$this->assertNotEmpty( $cache->get( 'bar' ) );
	}
	
}
