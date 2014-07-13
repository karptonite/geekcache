<?php
namespace GeekCache\Tag;

use GeekCache\Cache\Cache;

class TagFactory
{
    private $cache;

    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }

    public function makeTag($key)
    {
        return new Tag($this->cache, $key);
    }
}
