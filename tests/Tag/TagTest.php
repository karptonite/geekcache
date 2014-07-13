<?php
class TagTest extends PHPUnit_Framework_TestCase
{
    const KEY = 'theTag';

    public function setUp()
    {
        $this->cache = new GeekCache\Cache\ArrayCache;
        $this->tag = new GeekCache\Tag\Tag($this->cache, self::KEY);
    }

    public function testGetVersionReturnsVersion()
    {
        $version = $this->tag->getVersion();
        $this->assertNotNull($version);
    }

    public function testClearReturnsVersion()
    {
        $version = $this->tag->clear();
        $this->assertEquals($version, $this->tag->getVersion());
    }

    public function testClearClears()
    {
        $version = $this->tag->getVersion();
        $this->assertNotEquals($version, $this->tag->clear());
    }

    public function testTagsConsistent()
    {
        $tag2 = new GeekCache\Tag\Tag($this->cache, self::KEY);
        $this->tag->clear();
        $this->assertEquals($this->tag->getVersion(), $tag2->getVersion());
    }

    public function testKeysTrackedSeparately()
    {
        $tag2 = new GeekCache\Tag\Tag($this->cache, 'anotherKey');
        $this->assertNotEquals($this->tag->getVersion(), $tag2->getVersion());
    }
}
