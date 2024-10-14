<?php

namespace GeekCache\Cache;

abstract class AbstractFreshnessPolicy implements FreshnessPolicy
{
    public const POLICY_NAMESPACE = '';

    public function packValueWithPolicy($value, $ttl = 0)
    {
        return new CacheData($value, $this->createFreshnessData($ttl));
    }

    public function unpackValue($result)
    {
        if (!($result instanceof CacheData)) {
            return false;
        }

        return $result->getValue();
    }

    public function resultIsFresh($result)
    {
        if (!($result instanceof CacheData)) {
            return false;
        }

        $freshnessData = $result->getFreshnessData();
        return $this->isFresh($freshnessData);
    }

    public function getNamespace()
    {
        return static::POLICY_NAMESPACE;
    }

    public function stage()
    {
    }
}
