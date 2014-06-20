<?php
use Mockery as m;

class TagSetFactoryTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		$this->cache      = new GeekCache\Cache\ArrayCache();
		$tagFactory = new GeekCache\Cache\TagFactory( $this->cache );
		$this->factory    = new GeekCache\Cache\TagSetFactory( $tagFactory );
	}
	
	public function testTagSetFactoryMakesTagSet()
	{
		$tagset = $this->factory->makeTagSet( array( 'foo', 'bar' ) );
		$this->assertInstanceOf( 'GeekCache\Cache\TagSet', $tagset );
		
		$this->assertFalse( $this->cache->get( 'tag_foo' ) );
		$tagset->getSignature();
		$this->assertNotEmpty( $this->cache->get( 'tag_foo' ) );
		$this->assertNotEmpty( $this->cache->get( 'tag_bar' ) );
	}
	
	public function testAlternateMakeInterface()
	{
		$tagset = $this->factory->makeTagSet( 'foo', 'bar' );
	}
}
