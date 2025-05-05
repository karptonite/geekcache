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

    public function getSignature()
    {
        // we don't need to stage if we are getting only a single tag for writing
        if (count($this->tags) > 1) {
            $this->stage();
        }
        return $this->fetchSignature(true);
    }

    public function readSignature()
    {
        // we don't have to stage for reading (without regenerating),
        // because it will already be staged in the FreshnessPolicy.
        return $this->fetchSignature(false);
    }
    
    private function fetchSignature(bool $andGenerate)
    {
        $versions = [];
        foreach ($this->tags as $tag) {
            $versions[] = $andGenerate ? $tag->getVersion() :  $tag->readVersion();
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
