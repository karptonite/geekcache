<?php
use Mockery as m;

class TagFactoryTest extends PHPUnit\Framework\TestCase
{
    const TAGNAME = 'TheTag';
    const TAGKEY = 'tag_TheTag';

    public function setUp(): void
    {
        $this->cache = new GeekCache\Cache\ArrayCache();
        $this->factory = new GeekCache\Tag\TagFactory($this->cache);
    }

    public function tearDown():void
    {
        m::close();
        parent::tearDown();
    }
    public function testTagFactoryReturnsTag()
    {
        $tag = $this->factory->makeTag(static::TAGNAME);
    }

    public function testTagFactoryReturnsTagWithCorrectProperties()
    {
        $cache = m::mock('GeekCache\Cache\Cache');
        $cache->shouldReceive('get')
            ->with(static::TAGKEY)
            ->once()
            ->andReturn(false);

        $cache->shouldReceive('put')
            ->with(static::TAGKEY, m::any())
            ->once();

        $factory = new GeekCache\Tag\TagFactory($cache);

        $tag = $factory->makeTag(static::TAGNAME);
        $tag->getVersion();
    }
}
