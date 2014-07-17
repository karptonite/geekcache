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
        parent::put($key, $this->policy->packValueWithPolicy($value, $ttl), $this->policy->computeTtl($ttl));
    }

    public function get($key, callable $regenerator = null, $ttl = null)
    {
        $result              = null;
        $regeneratedByParent = false;

        $packedResult = $this->getFromParent($regeneratedByParent, $key, $regenerator, $ttl);

        if ($this->shouldRegenerate($packedResult, $regeneratedByParent)) {
            $result = $this->regenerate($key, $regenerator, $ttl);
        }

        if ($this->shouldReturnCachedData($packedResult, $result, $regeneratedByParent, $regenerator)) {
            $result = $this->policy->unpackValue($packedResult);
        }

        return isset($result) ? $result : false;
    }

    private function shouldRegenerate($packedResult, $regeneratedByParent)
    {
        return !$regeneratedByParent && !$this->policy->resultIsFresh($packedResult);
    }

    private function shouldReturnCachedData($packedResult, $result, $regeneratedByParent, $regenerator)
    {
        return $this->policy->resultIsFresh($packedResult) ||
            $regeneratedByParent ||
            ($result === false && is_callable($regenerator));
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
        return function ($staleDataAvailable = null) use ($policy, $regenerator, $ttl, &$regeneratedByParent) {
            $value = $staleDataAvailable ? $regenerator($staleDataAvailable) : $regenerator();
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
