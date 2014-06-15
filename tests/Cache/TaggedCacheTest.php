<?php
class TaggedCacheTest extends BaseCacheTest
{
	private $arraycache;

	public function setUp()
	{
		parent::setUp();
		$this->parentcache = new Geek\Cache\ArrayCache;
		$this->tagset = new TagSetStub;
		$this->cache = new Geek\Cache\TaggedCache( $this->parentcache, $this->tagset );
	}

	public function testCacheInvalidatesWhenHashChanges()
	{
		$this->cache->put( static::KEY, static::VALUE );
		$this->tagset->hash = 'bar';
		
		$this->assertFalse( $this->cache->get( static::KEY ) );
	}

	public function testGetStaleWhenHashChanges()
	{
		$this->cache->put( static::KEY, static::VALUE );
		$this->tagset->hash = 'bar';

		$this->assertEquals( static::VALUE, $this->cache->getStale( static::KEY ) );
	}

	public function testGetStaleFromWrappedSoftInvalidatables()
	{
		$this->cache = new Geek\Cache\SoftExpiringCache( $this->cache, null, $this->cache );

		$this->cache->put( static::KEY, static::VALUE, 1 );
		$this->tagset->hash = 'bar';

		$value      = $this->cache->get( static::KEY );
		$stalevalue = $this->cache->getStale( static::KEY );

		$this->assertFalse( $value );
		$this->AssertEquals( static::VALUE, $stalevalue );
	}

	public function testGetStaleFromWralledSoftInvalidatablesReverse()
	{
		$this->cache = new Geek\Cache\SoftExpiringCache( $this->parentcache );
		$this->cache = new Geek\Cache\TaggedCache( $this->cache, $this->tagset, $this->cache );
		$this->cache->put( static::KEY, static::VALUE, 0.01 );
		$this->assertEquals( static::VALUE, $this->cache->get( static::KEY ) );
		usleep( 11000 );
		
		$value      = $this->cache->get( static::KEY );
		$stalevalue = $this->cache->getStale( static::KEY );

		$this->assertFalse( $value );
		$this->AssertEquals( static::VALUE, $stalevalue );
	}
	
	
	
	
}

class TagSetStub implements Geek\Cache\TagSet
{
	public $hash = 'foo';
	public function getHash()
	{
		return $this->hash;
	}
}
