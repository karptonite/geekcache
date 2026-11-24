<?php

namespace GeekCache\Cache;

class SoftInvalidatableCache extends CacheDecorator
{
    private FreshnessPolicy $policy;

    public function __construct(Cache $cache, FreshnessPolicy $policy)
    {
        parent::__construct(new NamespacedCache($cache, $policy->getNamespace()));
        $this->policy = $policy;
    }

    public function put($key, $value, $ttl = 0)
    {
        return parent::put($key, $this->policy->packValueWithPolicy($value, $ttl), $this->policy->computeTtl($ttl));
    }

    public function stage(string $key, ?string $skipIfStaged = null): void
    {
        $this->policy->stage();
        parent::stage($key, $skipIfStaged);
    }

    public function get($key, ?callable $regenerator = null, $ttl = 0)
    {
        $regeneratedByParent = false;

        $packedResult = $this->getFromParent($regeneratedByParent, $key, $regenerator, $ttl);

        if ($this->policy->resultIsFresh($packedResult)) {
            return $this->policy->unpackValue($packedResult);
        }

        if (!$regeneratedByParent) {
            $result = $this->regenerateStale($key, $regenerator, $ttl);

            // if the results are false, either the data was not regenerated at all,
            // or it was queued for regeneration, and the data was not available
            if ($result !== false) {
                return $result;
            }
        }

        if ($this->wasQueuedForRegeneration($regenerator)) {
            return $this->policy->unpackValue($packedResult);
        }

        return false;
    }

    private function wasQueuedForRegeneration($regenerator)
    {
        return is_callable($regenerator);
    }

    private function getFromParent(&$regeneratedByParent, $key, ?callable $regenerator = null, $ttl = 0)
    {
        // Staging now (for tagged caches) ensures that the tags are gotten in the same request as the
        // main cache, in the event that their are other gets staged.
        // If the parent key is already staged, we assume that the tags are staged as well, and skip staging
        // This duty might be better handled by the policy directly, but this is the level at which we know
        // which key to pass into skipIfStaged.
        $this->policy->stage(skipIfStaged: $key);
        $wrappedRegenerator = $this->wrapRegenerator($regeneratedByParent, $regenerator, $ttl);
        return parent::get($key, $wrappedRegenerator, $this->policy->computeTtl($ttl));
    }

    private function wrapRegenerator(&$regeneratedByParent, ?callable $regenerator = null, $ttl = 0)
    {
        if (is_null($regenerator)) {
            return null;
        }

        // Passing information about whether stale data is available allows the regenerator to determine
        // whether to queue results with high or low priority
        return function ($staleDataAvailable) use ($regenerator, $ttl, &$regeneratedByParent) {
            $packedValue = $this->regenerateAndPack($regenerator, $staleDataAvailable, $ttl);
            $regeneratedByParent = true;
            return $packedValue;
        };
    }

    private function regenerateStale($key, ?callable $regenerator, $ttl)
    {
        if (is_null($regenerator)) {
            return false;
        }

        $packedValue = $this->regenerateAndPack($regenerator, true, $ttl);
        if ($packedValue === false) {
            return false;
        }

        parent::put($key, $packedValue, $this->policy->computeTtl($ttl));
        return $this->policy->unpackValue($packedValue);
    }

    // The packer is made before the regenerator runs, so a policy can take freshness data
    // from before the regenerator's reads.
    private function regenerateAndPack(callable $regenerator, bool $staleDataAvailable, $ttl)
    {
        $pack = $this->policy->packer($ttl);
        $value = $regenerator($staleDataAvailable);
        return $value === false ? false : $pack($value);
    }
}
