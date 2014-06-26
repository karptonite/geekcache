<?php
use Mockery as m;

class TagFactoryTest extends PHPUnit_Framework_TestCase
{
    const TAGNAME = 'TheTag';
    const TAGKEY = 'tag_TheTag';

    public function setUp()
    {
        $this->cache = new GeekCache\Cache\ArrayCache();
        $this->factory = new GeekCache\Cache\TagFactory( $this->cache );
    }

    public function testTagFactoryReturnsTag()
    {
        $tag = $this->factory->makeTag( static::TAGNAME );
    }
    
    public function testTagFactoryReturnsTagWithCorrectProperties()
    {
        $cache = m::mock( 'GeekCache\Cache\Cache' );
        $cache->shouldReceive( 'get' )
            ->with( static::TAGKEY )
            ->once()
            ->andReturn( false );

        $cache->shouldReceive( 'put' )
            ->with( static::TAGKEY, m::any() )
            ->once();
        
        $factory = new GeekCache\Cache\TagFactory( $cache );
        
        $tag = $factory->makeTag( static::TAGNAME );
        $tag->getVersion();
    }
}
    
