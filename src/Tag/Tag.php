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

    public function stage()
    {
        if (!$this->staged) {
            $this->cache->stage($this->key);
        }
        $this->staged = true;
    }

    public function getVersion()
    {
        $this->staged = false;
        $stored = $this->cache->get($this->key);
        return $stored && is_string($stored) ? $stored : $this->clear();
    }
    
    public function unstage()
    {
        if ($this->staged) {
            $this->cache->get($this->key);
        }
    }
    

    public function clear()
    {
        $version = uniqid();
        $this->cache->put($this->key, $version);
        return $version;
    }
}
