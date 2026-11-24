<?php

use GeekCache\Cache\MultiGetCache;
use GeekCache\Cache\StageableCache;

class StageableCacheTest extends PHPUnit\Framework\TestCase
{
    public const KEY    = 'thekey';
    public const VALUE  = 'foobar';
    public const KEY2   = 'thekey2';
    public const VALUE2 = 'foobar2';
    public const REGENERATED = 'regenerated';

    private $backend;
    private StageableCache $cache;

    public function setUp(): void
    {
        // Memcached with several servers returns a multi-get's keys grouped by
        // server, not in request order.
        $this->backend = new class () implements MultiGetCache {
            public array $values = [];
            public int $reads = 0;

            public function getMulti(array $keys): array
            {
                $this->reads++;
                $found = [];
                foreach (array_reverse($keys) as $key) {
                    if (array_key_exists($key, $this->values)) {
                        $found[$key] = $this->values[$key];
                    }
                }
                return $found;
            }

            public function get($key)
            {
                $this->reads++;
                return $this->values[$key] ?? false;
            }

            public function put($key, $value, $ttl = 0)
            {
                $this->values[$key] = $value;
                return true;
            }

            public function delete($key)
            {
                unset($this->values[$key]);
                return true;
            }

            public function clear()
            {
                $this->values = [];
                return true;
            }
        };
        $this->cache = new StageableCache($this->backend);
    }

    public function testGetWithStagedKeysFindsValueReturnedOutOfOrder()
    {
        $this->cache->put(self::KEY, self::VALUE);
        $this->cache->put(self::KEY2, self::VALUE2);
        $this->cache->stage(self::KEY2);

        $result = $this->cache->get(self::KEY, $this->failingRegenerator());

        $this->assertSame(self::VALUE, $result);
    }

    public function testStagedKeyIsServedFromTheSameMultiGet()
    {
        $this->cache->put(self::KEY, self::VALUE);
        $this->cache->put(self::KEY2, self::VALUE2);
        $this->cache->stage(self::KEY2);

        $this->cache->get(self::KEY);
        $result = $this->cache->get(self::KEY2, $this->failingRegenerator());

        $this->assertSame(self::VALUE2, $result);
        $this->assertSame(1, $this->backend->reads);
    }

    public function testGetWithStagedKeysRegeneratesMissingValue()
    {
        $this->cache->put(self::KEY2, self::VALUE2);
        $this->cache->stage(self::KEY2);

        $result = $this->cache->get(self::KEY, fn () => self::REGENERATED);

        $this->assertSame(self::REGENERATED, $result);
    }

    private function failingRegenerator(): callable
    {
        return function () {
            $this->fail('the regenerator ran for a cached value');
        };
    }
}
