<?php

namespace GeekCache\Cache;

class ArrayCache extends AbstractBaseCache implements Cache
{
    private $cache = array();
    private $maxputs;
    private $putcount = 0;

    public function __construct($maxputs = null)
    {
        $this->maxputs = (int)$maxputs;
    }

    public function get($key, callable $regenerator = null, $ttl = 0)
    {
        return $this->cacheExists($key) ? $this->cache[$key] : $this->regenerate($key, $regenerator, $ttl);
    }

    protected function cacheExists($key)
    {
        return array_key_exists($key, $this->cache);
    }

    public function put($key, $value, $ttl = 0)
    {
        if ($this->putIsPermitted($key)) {
            $this->cache[$key] = $value;
            $this->putcount++;
            return true;
        } else {
            return false;
        }
    }

    private function putIsPermitted($key)
    {
        return !$this->maxputs || $this->putcount < $this->maxputs || $this->cacheExists($key);
    }

    public function delete($key)
    {
        unset($this->cache[$key]);
    }

    public function clear()
    {
        $this->cache = array();
        return true;
    }
}
