<?php
namespace GeekCache\Cache;

class IncrementableNamespacedCache extends NamespacedCache implements IncrementableCache
{
    private $incrementablecache;

    public function __construct(IncrementableCache $incrementablecache, $namespace)
    {
        parent::__construct($incrementablecache, $namespace);
        $this->incrementablecache = $incrementablecache;
    }

    public function increment($key, $value = 1)
    {
        return $this->incrementablecache->increment($this->reviseKey($key), $value);
    }
}
