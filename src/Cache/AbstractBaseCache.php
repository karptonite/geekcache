<?php

namespace GeekCache\Cache;

abstract class AbstractBaseCache
{
    protected function regenerate($key, callable $regenerator = null, $ttl = 0)
    {
        $value = false;

        if (is_callable($regenerator)) {
            $value = $this->callRegenerator($regenerator);
            if ($value !== false) {
                $this->put($key, $value, $ttl);
            }
        }

        return $value;
    }

    protected function callRegenerator(callable $regenerator)
    {
        return $regenerator(false);
    }
}
