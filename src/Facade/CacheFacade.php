<?php
namespace GeekCache\Facade;

use GeekCache\Cache\CacheBuilder;
use GeekCache\Counter\CounterBuilder;
use GeekCache\Tag\TagSetFactory;

class CacheFacade
{
    private $cachebuilder;
    private $counterbuilder;
    private $tagSetFactory;

    public function __construct(CacheBuilder $cachebuilder, CounterBuilder $counterbuilder, TagSetFactory $tagSetFactory)
    {
        $this->cachebuilder = $cachebuilder;
        $this->counterbuilder = $counterbuilder;
        $this->tagSetFactory = $tagSetFactory;
    }

    public function cache()
    {
        return $this->cachebuilder;
    }

    public function counter()
    {
        return $this->counterbuilder;
    }

    public function clearTags($names)
    {
        $names = is_array($names) ? $names : func_get_args();
        $tagset = $this->tagSetFactory->makeTagSet($names);
        $tagset->clearAll();
    }
}
