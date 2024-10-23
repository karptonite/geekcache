<?php

namespace GeekCache\Cache;

use GeekCache\Tag\TagSet;

class TaggedFreshnessPolicy extends AbstractFreshnessPolicy
{
    public const POLICY_NAMESPACE = 'tg';
    private $tagset;

    public function __construct(TagSet $tagset)
    {
        $this->tagset = $tagset;
    }

    public function stage()
    {
        $this->tagset->stage();
    }

    public function computeTtl($ttl)
    {
        return $ttl;
    }
    
    public function resultIsFresh($result)
    {
        if (!($result instanceof CacheData)) {
            $this->tagset->unstage();
            return false;
        }
        return parent::resultIsFresh($result);
    }

    protected function isFresh($freshnessData)
    {
        return isset($freshnessData['signature']) && $freshnessData['signature'] == $this->tagset->getSignature();
    }

    protected function createFreshnessData($ttl)
    {
        return array('signature' => $this->tagset->getSignature());
    }
}
