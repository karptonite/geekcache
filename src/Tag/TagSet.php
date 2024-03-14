<?php
namespace GeekCache\Tag;
use GeekCache\Cache\Cache;

class TagSet
{
    private $tags = array();
    private Cache $cache;

    public function __construct(Cache $cache, array $tags = array())
    {
        foreach ($tags as $tag) {
            if (!(is_string($tag) )) {
                throw new \InvalidArgumentException("tags must be strings");
            }
        }

        $this->tags = array_map(fn($tag) => 'tag_' . $tag, array_values($tags));
        $this->cache = $cache;
    }

    public function getSignature()
    {
        $versions = $this->getVersions();

        return sha1(implode($versions));
    }
    
    private function getVersions()
    {
        $storedVersions = $this->cache->getMulti($this->tags);
        $versions = [];
        foreach( $storedVersions as $tag => $storedVersion ) {
            $versions[] = is_string($storedVersion) ? $storedVersion : $this->clear($tag);
        }
        return $versions;
    }

    public function clearAll()
    {
        array_walk($this->tags, function ($tag) {
            $this->clear($tag);
        });
    }
    
    private function clear($name)
    {
        $version = uniqid('',true);
        $this->cache->put($name, $version);
        return $version;
    }
}
