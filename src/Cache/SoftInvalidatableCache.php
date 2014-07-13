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
        $packedResult = parent::get($key, $this->wrapRegenerator($regenerator), $this->policy->computeTtl($ttl));

        if ($this->policy->resultIsFresh($packedResult)) {
            $result = $this->policy->unpackValue($packedResult);
        } elseif ($this->shouldRegenerate($packedResult, $regenerator)) {
            $result = $this->regenerate($key, $regenerator, $ttl);

            if ($this->regeneratedOffline($result, $regenerator)) {
                $result = $this->policy->unpackValue($packedResult);
            }
        }

        return isset($result) ? $result : false;
    }

    private function shouldRegenerate($packedResult, $regenerator = null)
    {
        return $packedResult !== false && is_callable($regenerator);
    }

    private function regeneratedOffline($result, $regenerator)
    {
        return $result === false && is_callable($regenerator);
    }

    private function wrapRegenerator(callable $regenerator = null, $ttl = null)
    {
        $policy = $this->policy;

        return is_null($regenerator) ? null : function () use ($policy, $regenerator, $ttl) {
            $value = $regenerator();
            return $value === false ? false : $policy->packValueWithPolicy($value, $ttl);
        };
    }
}
