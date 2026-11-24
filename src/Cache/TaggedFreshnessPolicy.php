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

    public function stage(?string $skipIfStaged = null)
    {
        if (is_null($skipIfStaged)) {
            $this->tagset->stage();
        } else {
            $this->tagset->stage(KeyReviser::reviseKey($this->getNamespace(), $skipIfStaged));
        }
    }

    public function computeTtl($ttl)
    {
        return $ttl;
    }

    public function resultIsFresh($result)
    {
        if (!($result instanceof CacheData)) {
            $this->tagset->decrementStagedCounts();
            return false;
        }
        return parent::resultIsFresh($result);
    }

    protected function isFresh($freshnessData)
    {
        return isset($freshnessData['signature']) && $freshnessData['signature'] == $this->tagset->readSignature();
    }

    // The signature is read when the packer is made, before the regenerator runs: read when
    // packing, it would mark the value fresh against an invalidation that landed while the
    // regenerator was reading.
    public function packer($ttl = 0): callable
    {
        $freshnessData = $this->createFreshnessData($ttl);
        return fn ($value) => new CacheData($value, $freshnessData);
    }

    protected function createFreshnessData($ttl)
    {
        return array('signature' => $this->tagset->getSignature());
    }
}
