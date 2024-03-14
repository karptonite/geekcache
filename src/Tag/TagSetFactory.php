<?php
namespace GeekCache\Tag;
use GeekCache\Cache\Cache;

class TagSetFactory
{
    private $cache;

    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }

    public function makeTagSet($names)
    {
        $names = is_array($names) ? $names : func_get_args();
        $names = array_unique($names);

        return new TagSet($this->cache, $names);
    }
}
