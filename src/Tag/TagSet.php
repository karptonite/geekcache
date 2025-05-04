<?php

namespace GeekCache\Tag;

class TagSet
{
    private $tags = array();

    public function __construct(array $tags = array())
    {
        foreach ($tags as $tag) {
            if (!($tag instanceof Tag)) {
                throw new \InvalidArgumentException("tags must contain instances of Tag");
            }
        }

        $this->tags = $tags;
    }

    // FIXME consider splitting this into getSignature and readSignature.
    // the former would be used when we need a signature for writing, and it will create
    // the tags if they do not exist. The later is used when reading only. We don't need to
    // create the tag in this case.
    public function getSignature()
    {
        $versions = array();

        // make sure all of the tags are staged before getting, so that we only do one get.
        if (count($this->tags) > 1) {
            foreach ($this->tags as $tag) {
                $tag->stage();
            }
        }

        foreach ($this->tags as $tag) {
            $versions[] = $tag->getVersion();
        }

        return sha1(implode($versions));
    }

    public function clearAll()
    {
        array_walk($this->tags, function ($tag) {
            $tag->clear();
        });
    }

    public function stage(?string $skipIfStaged = null)
    {
        foreach ($this->tags as $tag) {
            $tag->stage($skipIfStaged);
        }
    }
    
    public function decrementStagedCounts()
    {
        array_walk($this->tags, function ($tag) {
            $tag->decrementStagedCount();
        });
    }
}
