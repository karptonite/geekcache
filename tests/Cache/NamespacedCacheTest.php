<?php
use Mockery as m;
class NamespacedCacheTest extends BaseCacheTest
{
    private $arraycache;

    const CACHE_NAMESPACE = "ns";

    public function setUp()
    {
        parent::setUp();
        $this->parentcache = new GeekCache\Cache\ArrayCache;
        $this->cache = new GeekCache\Cache\NamespacedCache( $this->parentcache, self::CACHE_NAMESPACE );
    }

    public function testNamespaceAdded()
    {
        $this->cache->put( 'foo', 'bar' );
        $this->assertEquals( 'bar', $this->parentcache->get( self::CACHE_NAMESPACE . '_foo' ) );
    }
    
}
