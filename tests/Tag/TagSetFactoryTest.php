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

    public function testTagSetFactoryCollapsesDuplicates()
    {
        $tagmock = m::mock( 'GeekCache\Cache\Tag' );
        $tagFactoryMock = m::mock( 'GeekCache\Cache\TagFactory' );
        $tagFactoryMock->shouldReceive( 'makeTag' )->with( 'foo' )->once()->andReturn( $tagmock );
        $tagFactoryMock->shouldReceive( 'makeTag' )->with( 'bar' )->once()->andReturn( $tagmock );

        $factory = new GeekCache\Cache\TagSetFactory( $tagFactoryMock );
        $factory->makeTagSet( 'foo', 'bar', 'foo' );
    }
    
    
    public function testAlternateMakeInterface()
    {
        $tagset = $this->factory->makeTagSet( 'foo', 'bar' );
    }
}
