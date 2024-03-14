<?php
use Mockery as m;

class TagSetTest extends PHPUnit_Framework_TestCase
{
    const KEY = 'theTag';

    public function setUp()
    {
        parent::setUp();
        $this->cache = new GeekCache\Cache\ArrayCache;
        $this->tagset = $this->getNewTagSet();
        $this->tags = $this->getArrayOfTags(2);
    }

    private function getArrayOfTags($quantity)
    {
        $tags = array();
        for ($i = 0; $i < $quantity; $i++) {
            $tags[] =  'key' . $i;
        }
        return $tags;
    }

    private function getNewTagSet()
    {
        $tags = $this->getArrayOfTags(2);
        return new GeekCache\Tag\TagSet($this->cache, $tags);
    }

    public function testTagSetReturnsConsistentHash()
    {
        $hash = $this->tagset->getSignature();
        $tagset2 = $this->getNewTagSet();
        $this->assertNotNull($hash);
        $this->assertEquals($hash, $tagset2->getSignature());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testTagSetEnforcesType()
    {
        $tags = $this->getArrayOfTags(2);
        $tags[] = new GeekCache\Cache\ArrayCache();
        $tagset = new GeekCache\Tag\TagSet($this->cache, $tags);
    }

    public function testTagSetHashChangesWhenTagIsCleared()
    {
        $hash = $this->tagset->getSignature();
        $tagset = new GeekCache\Tag\TagSet($this->cache, [$this->tags[0]]);
        $tagset->clearAll();
        $this->assertNotEquals($hash, $this->tagset->getSignature());
    }

    public function testTagSetHashChangesWhenAnyTagIsCleared()
    {
        $hash = $this->tagset->getSignature();
        $tagset = new GeekCache\Tag\TagSet($this->cache, [$this->tags[1]]);
        $tagset->clearAll();
        $this->assertNotEquals($hash, $this->tagset->getSignature());
    }
    
    public function testKeysTrackedSeparately()
    {
        $tagset = new GeekCache\Tag\TagSet($this->cache, ['anotherKey', 'yetAnotherKey']);
        $this->assertNotEquals($this->tagset->getSignature(), $tagset->getSignature());
    }

    public function testHashesAreConsistentLength()
    {
        $hash = $this->tagset->getSignature();
        $tags2 = $this->getArrayOfTags(3);
        $tagset = new GeekCache\Tag\TagSet($this->cache, $tags2);
        $hash2 = $tagset->getSignature();
        $this->assertNotEquals($hash, $hash2);
        $this->assertEquals(strlen($hash), strlen($hash2));
    }
}
