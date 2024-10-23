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
        $versions = array();

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

    public function stage()
    {
        array_walk($this->tags, function ($tag) {
            $tag->stage();
        });
    }
    
    public function unstage()
    {
        array_walk($this->tags, function ($tag) {
            $tag->unstage();
        });
    }
}
