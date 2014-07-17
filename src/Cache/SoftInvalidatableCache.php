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
        $regenerating       = false;
        $wrappedRegenerator = $this->wrapRegenerator($regenerating, $regenerator, $ttl);
        $packedResult       = parent::get($key, $wrappedRegenerator, $this->policy->computeTtl($ttl));

        if ($this->policy->resultIsFresh($packedResult)) {
            $result = $this->policy->unpackValue($packedResult);
        } elseif ($this->shouldRegenerate($regenerating, $packedResult, $regenerator)) {
            $result = $this->regenerate($key, $regenerator, $ttl);
        }

        // if a process has been queued to refesh the data, return whatever
        // data we have, even if it is not fresh
        if ($this->regeneratingOffline($regenerating, $result, $regenerator)) {
            $result = $this->policy->unpackValue($packedResult);
        }

        return isset($result) ? $result : false;
    }

    private function shouldRegenerate($regenerating, $packedResult, $regenerator = null)
    {
        return !$regenerating && $packedResult !== false && is_callable($regenerator);
    }

    private function regeneratingOffline($regenerating, $result, $regenerator)
    {
        return $regenerating || ($result === false && is_callable($regenerator));
    }

    private function wrapRegenerator(&$regenerating, callable $regenerator = null, $ttl = null)
    {
        if (is_null($regenerator)) {
            return null;
        }

        //PHP 5.4 won't allow $this to be passed in use statements in closure
        $policy = $this->policy;

        return function ($dataAvailable = null) use ($policy, $regenerator, $ttl, &$regenerating) {
            $value = $dataAvailable ? $regenerator($dataAvailable) : $regenerator();
            if ($value === false) {
                $regenerating = true;
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
