<?php
class TagSetTest extends PHPUnit_Framework_TestCase
{
	const KEY = 'theTag';

	public function setUp()
	{
		$this->cache = new Geek\Cache\ArrayCache;
		$this->tagset = $this->getNewTagSet();
		$this->tags = $this->getArrayOfTags( 2 );
	}

	private function getArrayOfTags( $quantity )
	{
		$tags = array();
		for( $i = 0; $i < $quantity; $i++ )
			$tags[] = new Geek\Cache\Tag( $this->cache, 'key' . $i );
		return $tags;
	}

	private function getNewTagSet()
	{
		$tags = $this->getArrayOfTags( 2 );
		return new Geek\Cache\TagSetImpl( $tags );
	}

	public function testTagSetReturnsConsistentHash()
	{
		$hash = $this->tagset->getHash();
		$tagset2 = $this->getNewTagSet();
		$this->assertNotNull( $hash );
		$this->assertEquals( $hash, $tagset2->getHash() );
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testTagSetEnforcesType()
	{
		$tags = $this->getArrayOfTags( 2 );
		$tags[] = new Geek\Cache\ArrayCache();
		$tagset = new Geek\Cache\TagSetImpl( $tags );
	}

	public function testTagSetHashChangesWhenTagIsCleared()
	{
		$hash = $this->tagset->getHash();
		$this->tags[0]->clear();
		$this->assertNotEquals( $hash, $this->tagset->getHash() );
	}

	public function testTagSetHashChangesWhenAnyTagIsCleared()
	{
		$hash = $this->tagset->getHash();
		$this->tags[1]->clear();
		$this->assertNotEquals( $hash, $this->tagset->getHash() );
	}

	public function testHashesAreConsistentLength()
	{
		$hash = $this->tagset->getHash();
		$tags2 = $this->getArrayOfTags( 3 );
		$tagset = new Geek\Cache\TagSetImpl( $tags2 );
		$hash2 = $tagset->getHash();
		$this->assertNotEquals( $hash, $hash2 );
		$this->assertEquals( strlen( $hash ), strlen( $hash2 ) );
	}
}
