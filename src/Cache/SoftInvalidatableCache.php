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
        $result             = null;
        $regenerated        = false;
        $wrappedRegenerator = $this->wrapRegenerator($regenerated, $regenerator, $ttl);
        $packedResult       = parent::get($key, $wrappedRegenerator, $this->policy->computeTtl($ttl));

        if ($this->policy->resultIsFresh($packedResult)) {
            $result = $this->policy->unpackValue($packedResult);
        } elseif (!$regenerated) {
            $result = $this->regenerate($key, $regenerator, $ttl);
        }

        // if a process has been queued to refesh the data, return whatever
        // data we have, even if it is not fresh
        if ($this->regeneratedOffline($regenerated, $result, $regenerator)) {
            $result = $this->policy->unpackValue($packedResult);
        }

        return isset($result) ? $result : false;
    }

    private function regeneratedOffline($regenerated, $result, $regenerator)
    {
        return $regenerated || ($result === false && is_callable($regenerator));
    }

    private function wrapRegenerator(&$regenerated, callable $regenerator = null, $ttl = null)
    {
        if (is_null($regenerator)) {
            return null;
        }

        //PHP 5.4 won't allow $this to be passed in use statements in closure
        $policy = $this->policy;

        return function ($staleDataAvailable = null) use ($policy, $regenerator, $ttl, &$regenerated) {
            $value       = $staleDataAvailable ? $regenerator($staleDataAvailable) : $regenerator();
            $regenerated = true;

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
