<?php
namespace GeekCache\Cache;

/**
 * If PHP supported it, this would be a private class for AbstractFreshnessPolicy
 * It is not intended for use elsewhere.
 */
class CacheData
{
    private $value;
    private $freshnessData;

    public function __construct($value, $freshnessData)
    {
        $this->value = $value;
        $this->freshnessData = $freshnessData;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getFreshnessData()
    {
        return $this->freshnessData;
    }
}
