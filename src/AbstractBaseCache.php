<?php
namespace GeekCache\Cache;

abstract class AbstractBaseCache
{
    protected function regenerate($key, callable $regenerator = null, $ttl = null)
    {
        $value = false;

        if (is_callable($regenerator)) {
            $value = $regenerator();
            if ($value !== false) {
                $this->put($key, $value, $ttl);
            }
        }

        return $value;
    }
}
