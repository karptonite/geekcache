<?php
namespace GeekCache\Cache;

class NamespacedCache extends CacheDecorator
{
    private $namespace;

    public function __construct(Cache $cache, $namespace)
    {
        parent::__construct($cache);
        $this->namespace = $namespace;
    }

    public function get($key, callable $regenerator = null, $ttl = null)
    {
        return parent::get($this->reviseKey($key), $regenerator, $ttl);
    }

    public function put($key, $value, $ttl = null)
    {
        return parent::put($this->reviseKey($key), $value, $ttl);
    }

    public function delete($key)
    {
        return parent::delete($this->reviseKey($key));
    }

    public function clear()
    {
        return parent::clear();
    }

    protected function reviseKey($key)
    {
        return $this->namespace . '_' . $key;
    }
}
