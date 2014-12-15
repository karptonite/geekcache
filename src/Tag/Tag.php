<?php
namespace GeekCache\Tag;

use GeekCache\Cache\Cache;

class Tag
{
    private $key;
    private $cache;

    public function __construct(Cache $cache, $name)
    {
        $this->cache = $cache;
        $this->key = 'tag_' . $name;
    }

    public function getVersion()
    {
        $stored = $this->cache->get($this->key);
        return $stored && is_string($stored) ? $stored : $this->clear();
    }

    public function clear()
    {
        $version = uniqid();
        $this->cache->put($this->key, $version);
        return $version;
    }
}
