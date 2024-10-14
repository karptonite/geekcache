<?php

namespace GeekCache\Cache;

use GeekCache\Tag\TagSetFactory;

class CacheClearer
{
    private $tagSetFactory;
    private $persistentCache;
    private $localCaches;

    public function __construct(TagSetFactory $tagSetFactory, Cache $persistentCache, array $localCaches = array())
    {
        $this->tagSetFactory = $tagSetFactory;
        $this->persistentCache = $persistentCache;
        $this->localCaches = $localCaches;
    }

    public function clearTags($names)
    {
        $names = is_array($names) ? $names : func_get_args();
        $tagset = $this->tagSetFactory->makeTagSet($names);
        $tagset->clearAll();
    }

    public function flush()
    {
        $this->persistentCache->clear();
        $this->flushLocal();
    }

    public function flushLocal()
    {
        foreach ($this->localCaches as $localCache) {
            $localCache->clear();
        }
    }
}
