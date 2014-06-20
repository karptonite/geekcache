<?php
use Mockery as m;

class CacheBuilderTest extends PHPUnit_Framework_TestCase
{
	public function prepareFullMockBuilder()
	{
		$this->cache         = m::mock( 'GeekCache\Cache\Cache' );
		$this->memocache     = m::mock( 'GeekCache\Cache\Cache' );
		$this->tagsetfactory = m::mock( 'GeekCache\Cache\TagSetFactory' );
		
		$this->builder = new GeekCache\Cache\CacheBuilder( $this->cache, $this->memocache, $this->tagsetfactory );
	}

	public function prepareArrayBuilder()
	{
		$this->cache = new GeekCache\Cache\ArrayCache();
		$this->memocache = new GeekCache\Cache\ArrayCache();
		$this->tagcache = new GeekCache\Cache\Arraycache();
		$this->tagfactory = new GeekCache\Cache\TagFactory( $this->tagcache );
		$this->tagsetfactory = new GeekCache\Cache\TagSetFactory( $this->tagfactory );
		$this->builder = new GeekCache\Cache\CacheBuilder( $this->cache, $this->memocache, $this->tagsetfactory );
	}
	
	public function testBuildBasic()
	{
		$this->prepareFullMockBuilder();
		$this->cache->shouldReceive( 'get' )->with( 'foo' )->once();
		$cache = $this->builder->make();
		$cache->get('foo');
	}

	public function testMemoizeBuild()
	{
		$this->prepareFullMockBuilder();
		$this->memocache->shouldReceive( 'get' )->with( 'foo' )->once()->andReturn( false );
		$this->memocache->shouldReceive( 'put' )->with( 'foo', 'bar' )->once();
		$this->cache->shouldReceive( 'get' )->with( 'foo' )->once()->andReturn( 'bar' );
		$cache = $this->builder->memoize()->make();
		$this->assertInstanceOf( 'GeekCache\Cache\MemoizedCache', $cache );
		$cache->get( 'foo' );
	}

	public function testDoubleDecorate()
	{
		$this->prepareFullMockBuilder();
		$this->memocache->shouldReceive( 'get' )->with( 'foo' )->twice()->andReturn( false );
		$this->memocache->shouldReceive( 'put' )->with( 'foo', 'bar' )->twice();
		$this->cache->shouldReceive( 'get' )->with( 'foo' )->once()->andReturn( 'bar' );
		$cache = $this->builder->memoize()->memoize()->make();
		$this->assertInstanceOf( 'GeekCache\Cache\MemoizedCache', $cache );
		$cache->get( 'foo' );
	}

	public function testTripleDecorate()
	{
		$this->prepareFullMockBuilder();
		$this->memocache->shouldReceive( 'get' )->with( 'foo' )->times( 3 )->andReturn( false );
		$this->memocache->shouldReceive( 'put' )->with( 'foo', 'bar' )->times( 3 );
		$this->cache->shouldReceive( 'get' )->with( 'foo' )->once()->andReturn( 'bar' );
		$cache = $this->builder->memoize()->memoize()->memoize()->make();
		$this->assertInstanceOf( 'GeekCache\Cache\MemoizedCache', $cache );
		$cache->get( 'foo' );
	}

	public function testTaggedBuild()
	{
		$this->prepareFullMockBuilder();
		$this->cache->shouldReceive( 'get' )->with( 'foo' )->andReturnNull();
		$tagset = m::mock( 'GeekCache\Cache\TagSet' );
		$this->tagsetfactory->shouldReceive( 'makeTagSet' )
			->once()
			->with( array( 'footag', 'bartag' ) )
			->andReturn( $tagset );

		$cache = $this->builder->addTags( array( 'footag', 'bartag' ) )->make();
		$this->assertInstanceOf( 'GeekCache\Cache\SoftInvalidatableCache', $cache );
		$cache->get( 'foo' );

		$tagset->shouldReceive( 'getSignature' )->once()->andReturn( 'signature' );
		$spy = null;
		$this->cache->shouldReceive( 'put' )->once()->with( 'foo', m::any(), 5 )->andReturnUsing( function( $arg1, $arg2, $arg3 ) use( &$spy ){$spy = $arg2;} );

		$result = $cache->put( 'foo', 'bar', 5 );
		$freshnessData = $spy->getFreshnessData();
		$this->assertEquals( 'signature', $freshnessData['signature'] );
	}

	public function testAlternateAddTagsInterface()
	{
		$this->prepareFullMockBuilder();
		$tagset = m::mock( 'GeekCache\Cache\TagSet' );
		$this->tagsetfactory->shouldReceive( 'makeTagSet' )
			->once()
			->with( array( 'footag', 'bartag' ) )
			->andReturn( $tagset );

		$cache = $this->builder->addTags( 'footag', 'bartag' )->make();
	}
	

	/**
	 * @group slowTests
	 */
	public function testGracePeriod()
	{
		$this->prepareArrayBuilder();
		$cache = $this->builder->addGracePeriod( 0 )->make();
		$cache->put( 'foo', 'bar', 1 );
		$this->assertEquals( 'bar', $cache->get( 'foo' ) );
		usleep( 2100000 );
		$this->assertFalse( $cache->get( 'foo' ) );
		$this->assertEquals( 'bar', $cache->getStale( 'foo' ) );
	}

	public function testCombinedCache()
	{
		$this->prepareArrayBuilder();
		$cache = $this->builder->addTags( array( 'footag', 'bartag' ))->addGracePeriod( 0 )->make();
		$cache->put( 'foo', 'bar', 1 );
		$this->tagfactory->makeTag( 'bartag' )->clear();
		$this->assertFalse( $cache->get( 'foo' ) );
		$this->assertEquals( 'bar', $cache->getStale( 'foo' ) );
	}
}
