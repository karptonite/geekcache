<?php
use Mockery as m;

class TagSetFactoryTest extends PHPUnit\Framework\TestCase
{
    private $cache;
    private $factory;
    public function setUp(): void
    {
        $this->cache      = new GeekCache\Cache\ArrayCache();
        $tagFactory = new GeekCache\Tag\TagFactory($this->cache);
        $this->factory    = new GeekCache\Tag\TagSetFactory($tagFactory);
    }

    public function tearDown(): void
    {
        m::close();
        parent::tearDown();
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

    public function testTagSetFactoryCollapsesDuplicates()
    {
        $tagmock = m::mock('GeekCache\Tag\Tag');
        $tagFactoryMock = m::mock('GeekCache\Tag\TagFactory');
        $tagFactoryMock->shouldReceive('makeTag')->with('foo')->once()->andReturn($tagmock);
        $tagFactoryMock->shouldReceive('makeTag')->with('bar')->once()->andReturn($tagmock);

        $factory = new GeekCache\Tag\TagSetFactory($tagFactoryMock);
        $factory->makeTagSet('foo', 'bar', 'foo');
    }

    public function testAlternateMakeInterface()
    {
        $tagset = $this->factory->makeTagSet('foo', 'bar');
    }
}
