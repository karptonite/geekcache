<?php

namespace GeekCache\Cache;

class ArrayCache extends AbstractBaseCache implements CheckableCache
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
        return $this->has($key) ? $this->cache[$key] : $this->regenerate($key, $regenerator, $ttl);
    }

    public function has($key): bool
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
        return !$this->maxputs || $this->putcount < $this->maxputs || $this->has($key);
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
    
    public function stage(string $key):void
    {
    }

    public function unstage(string $key):void
    {
    }
    
    public function getGetCount():int
    {
        return 0;
    }
}
