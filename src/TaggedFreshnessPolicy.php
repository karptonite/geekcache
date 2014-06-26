<?php
namespace GeekCache\Cache;

class TaggedFreshnessPolicy extends AbstractFreshnessPolicy
{
    private $tagset;

    public function __construct(TagSet $tagset)
    {
        $this->tagset = $tagset;
    }

    public function computeTtl($ttl)
    {
        return $ttl;
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
