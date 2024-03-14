<?php
use Mockery as m;

class TagSetFactoryTest extends PHPUnit_Framework_TestCase
{
    const TAGNAME = 'TheTag';
    const TAGKEY = 'tag_TheTag';
    
    public function setUp()
    {
        $this->cache      = new GeekCache\Cache\ArrayCache();
        $this->factory    = new GeekCache\Tag\TagSetFactory($this->cache);
    }

    public function testTagSetFactoryMakesTagSet()
    {
        $tagset = $this->factory->makeTagSet(array('foo', 'bar'));
        $this->assertInstanceOf('GeekCache\Tag\TagSet', $tagset);

        $this->assertFalse($this->cache->get('tag_foo'));
        $tagset->getSignature();
        $this->assertNotEmpty($this->cache->get('tag_foo'));
        $this->assertNotEmpty($this->cache->get('tag_bar'));
    }

    public function testTagSetFactoryReturnsTagSetWithCorrectProperties()
    {
        $cache = m::mock('GeekCache\Cache\Cache');
        $cache->shouldReceive('getMulti')
            ->with([static::TAGKEY])
            ->once()
            ->andReturn([static::TAGKEY => false]);

        $cache->shouldReceive('put')
            ->with(static::TAGKEY, m::any())
            ->once();

        $factory = new GeekCache\Tag\TagSetFactory($cache);

        $tag = $factory->makeTagSet([static::TAGNAME]);
        $tag->getSignature();
    }

    public function testTagSetFactoryCollapsesDuplicates()
    {
        $cache = m::mock('GeekCache\Cache\Cache');
        $cache->shouldReceive('getMulti')
            ->with(['tag_foo', 'tag_bar'])
            ->once()
            ->andReturn(['tag_foo' => false, 'tag_bar' => false ]);
        
        $cache->shouldReceive('put')
            ->with(m::any(), m::any())
            ->times(2);
        
        $factory = new GeekCache\Tag\TagSetFactory($cache);
        $tagset = $factory->makeTagSet(['foo', 'bar', 'foo']);
        $tagset->getSignature();
    }

    public function testAlternateMakeInterface()
    {
        $tagset = $this->factory->makeTagSet('foo', 'bar');
    }
}
