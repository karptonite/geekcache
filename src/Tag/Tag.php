<?php

namespace GeekCache\Tag;

use GeekCache\Cache\Cache;

class Tag
{
    private $key;
    private $cache;
    private $staged = false;

    public function __construct(Cache $cache, $name)
    {
        $this->cache = $cache;
        $this->key = 'tag_' . $name;
    }

    public function stage(?string $skipIfStaged = null)
    {
        // ensure that any given Tag object is only staged once (although the tag key may be staged multiple times).
        // There are three times a tag might be staged: First, if an entire
        // request (data and tag) are being staged, it will staged at that time.
        // Second, Any time a Tagged Cache is read, we stage the tags right before
        // reading.
        // Third, when a TagSet is read, we stage all of its tags before reading.
        //  But we only read once. This helps ensure that we don't "double stage" for
        // pre-staged Tagged caches.
        if (!$this->staged) {
            $this->cache->stage($this->key, $skipIfStaged);
        }
        $this->staged = true;
    }

    public function getVersion()
    {
        $this->staged = false;
        $stored = $this->cache->get($this->key);
        return $stored && is_string($stored) ? $stored : $this->clear();
    }
    
    public function decrementStagedCount()
    {
        if ($this->staged) {
            $this->cache->decrementStagedCount($this->key);
        }
    }

    public function clear()
    {
        $version = uniqid();
        $this->cache->put($this->key, $version);
        return $version;
    }
}
