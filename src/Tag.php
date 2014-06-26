<?php
namespace GeekCache\Cache;

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
        return $this->cache->get($this->key) ?: $this->clear();
    }

    public function clear()
    {
        $version = uniqid();
        $this->cache->put($this->key, $version);
        return $version;
    }
}
