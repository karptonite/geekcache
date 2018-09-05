<?php
namespace GeekCache\Cache;

class SoftInvalidatableCache extends CacheDecorator
{
    private $policy;

    public function __construct(Cache $cache, FreshnessPolicy $policy)
    {
        parent::__construct($cache);
        $this->policy = $policy;
    }

    public function put($key, $value, $ttl = null)
    {
        return parent::put($key, $this->policy->packValueWithPolicy($value, $ttl), $this->policy->computeTtl($ttl));
    }

    public function get($key, callable $regenerator = null, $ttl = null)
    {
        $regeneratedByParent = false;

        $packedResult = $this->getFromParent($regeneratedByParent, $key, $regenerator, $ttl);

        if($this->policy->resultIsFresh($packedResult)) {
            return $this->policy->unpackValue($packedResult);
        }

        if (!$regeneratedByParent) {
            $result = $this->regenerate($key, $regenerator, $ttl);

            // if the results are false, either the data was not regenerated at all,
            // or it was queued for regeneration, and the data was not available
            if($result !== false) {
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

    private function getFromParent(&$regeneratedByParent, $key, callable $regenerator = null, $ttl = null)
    {
        $wrappedRegenerator = $this->wrapRegenerator($regeneratedByParent, $regenerator, $ttl);
        return parent::get($key, $wrappedRegenerator, $this->policy->computeTtl($ttl));
    }

    private function wrapRegenerator(&$regeneratedByParent, callable $regenerator = null, $ttl = null)
    {
        if (is_null($regenerator)) {
            return null;
        }

        //PHP 5.4 won't allow $this to be passed in use statements in closure
        $policy = $this->policy;

        // Passing information about whether stale data is available allows the regenerator to determine
        // whether to queue results with high or low priority
        return function ($staleDataAvailable) use ($policy, $regenerator, $ttl, &$regeneratedByParent) {
            $value = $regenerator($staleDataAvailable);
            $regeneratedByParent = true;

            if ($value === false) {
                return false;
            } else {
                return $policy->packValueWithPolicy($value, $ttl);
            }
        };
    }

    protected function callRegenerator(callable $regenerator)
    {
        return $regenerator(true);
    }
}
