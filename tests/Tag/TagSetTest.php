<?php
use Mockery as m;

class TagSetTest extends PHPUnit\Framework\TestCase
{
    const KEY = 'theTag';
    private $tags;
    private $cache;
    private $tagset;

    public function setUp(): void
    {
        parent::setUp();
        $this->cache = new GeekCache\Cache\ArrayCache;
        $this->tagset = $this->getNewTagSet();
        $this->tags = $this->getArrayOfTags(2);
    }

    public function tearDown(): void
    {
        m::close();
        parent::tearDown();
    }
    
    private function getArrayOfTags($quantity)
    {
        $tags = array();
        for ($i = 0; $i < $quantity; $i++) {
            $tags[] = new GeekCache\Tag\Tag($this->cache, 'key' . $i);
        }
        return $tags;
    }

    private function getNewTagSet()
    {
        $tags = $this->getArrayOfTags(2);
        return new GeekCache\Tag\TagSet($tags);
    }

    public function testTagSetReturnsConsistentHash()
    {
        $hash = $this->tagset->getSignature();
        $tagset2 = $this->getNewTagSet();
        $this->assertNotNull($hash);
        $this->assertEquals($hash, $tagset2->getSignature());
    }

    public function testTagSetEnforcesType()
    {
        $this->expectException(InvalidArgumentException::class);
        $tags = $this->getArrayOfTags(2);
        $tags[] = new GeekCache\Cache\ArrayCache();
        $tagset = new GeekCache\Tag\TagSet($tags);
    }

    public function testTagSetHashChangesWhenTagIsCleared()
    {
        $hash = $this->tagset->getSignature();
        $this->tags[0]->clear();
        $this->assertNotEquals($hash, $this->tagset->getSignature());
    }

    public function testTagSetHashChangesWhenAnyTagIsCleared()
    {
        $hash = $this->tagset->getSignature();
        $this->tags[1]->clear();
        $this->assertNotEquals($hash, $this->tagset->getSignature());
    }

    public function testHashesAreConsistentLength()
    {
        $hash = $this->tagset->getSignature();
        $tags2 = $this->getArrayOfTags(3);
        $tagset = new GeekCache\Tag\TagSet($tags2);
        $hash2 = $tagset->getSignature();
        $this->assertNotEquals($hash, $hash2);
        $this->assertEquals(strlen($hash), strlen($hash2));
    }

    public function testClearAll()
    {
        $tags = array();
        for ($i = 0; $i < 2; $i++) {
            $tag = m::mock('GeekCache\Tag\Tag');
            $tag->shouldReceive('clear')->once();
            $tags[] = $tag;
        }

        $tagset = new GeekCache\Tag\TagSet($tags);
        $tagset->clearAll();

    }
}
