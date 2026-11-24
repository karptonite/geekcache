<?php

use GeekCache\Cache\ArrayCache;
use GeekCache\Cache\CacheBuilder;
use GeekCache\Cache\MemoizedCache;
use GeekCache\Cache\MultiGetCache;
use GeekCache\Cache\NullCache;
use GeekCache\Cache\StageableCache;
use GeekCache\Tag\TagFactory;
use GeekCache\Tag\TagSetFactory;

/**
 * Wires caches as CacheServiceProvider does over one shared backend, so each
 * builder() is a separate PHP process: a worker (geekcache.nolocalcache) or a
 * web request (local memo caches). Writing the backend directly is another
 * process invalidating a tag.
 */
class TaggedCacheRegenerationRaceTest extends PHPUnit\Framework\TestCase
{
    private const KEY = 'thekey';
    private const REGENERATED_DURING = 'regenerated during the invalidation';
    private const REGENERATED_AFTER = 'regenerated after';

    private $backend;

    public function setUp(): void
    {
        $this->backend = new class () implements MultiGetCache {
            public array $values = [];

            public function getMulti(array $keys): array
            {
                return array_intersect_key($this->values, array_flip($keys));
            }

            public function get($key)
            {
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
    }

    public static function processes(): array
    {
        return [
            'worker, two tags' => [true, ['tag1', 'tag2']],
            'worker, one tag' => [true, ['tag1']],
            'web request, two tags' => [false, ['tag1', 'tag2']],
            'web request, one tag' => [false, ['tag1']],
        ];
    }

    /**
     * @dataProvider processes
     */
    public function testInvalidationDuringStaleRegenerationLeavesEntryStale(bool $worker, array $tags)
    {
        $this->item($worker, $tags)->put('original');
        $this->invalidateElsewhere('tag1');

        $this->assertRegenerationRacingAnInvalidationLeavesEntryStale($worker, $tags);
    }

    /**
     * @dataProvider processes
     */
    public function testInvalidationDuringColdRegenerationLeavesEntryStale(bool $worker, array $tags)
    {
        $this->item($worker, $tags)->put('original');
        $this->backend->delete('tg_' . self::KEY);

        $this->assertRegenerationRacingAnInvalidationLeavesEntryStale($worker, $tags);
    }

    /**
     * @dataProvider processes
     */
    public function testInvalidationDuringColdRegenerationWithNoStoredTagVersionLeavesEntryStale(bool $worker, array $tags)
    {
        $this->assertRegenerationRacingAnInvalidationLeavesEntryStale($worker, $tags);
    }

    public function testInvalidationAfterANestedCacheSharingATagRegeneratesLeavesEntryStale()
    {
        $this->backend->put('tag_tag1', 'before');
        $builder = $this->builder(true);
        $inner = $builder->addTags(['tag1'])->make('inner');

        $builder->addTags(['tag1'])->make(self::KEY)->get(function () use ($inner) {
            $inner->get(fn () => 'inner value');
            $this->invalidateElsewhere('tag1');
            return self::REGENERATED_DURING;
        });

        $this->assertSame(self::REGENERATED_AFTER, $this->item(true, ['tag1'])->get(fn () => self::REGENERATED_AFTER));
    }

    public function testInvalidationDuringStaleRegenerationUnderAGracePeriodLeavesEntryStale()
    {
        $item = fn () => $this->builder(true)->addGracePeriod()->addTags(['tag1'])->make(self::KEY);
        $item()->put('original');
        $this->invalidateElsewhere('tag1');

        $item()->get(function () {
            $this->invalidateElsewhere('tag1');
            return self::REGENERATED_DURING;
        });

        $this->assertSame(self::REGENERATED_AFTER, $item()->get(fn () => self::REGENERATED_AFTER));
    }

    private function assertRegenerationRacingAnInvalidationLeavesEntryStale(bool $worker, array $tags): void
    {
        $result = $this->item($worker, $tags)->get(function () {
            $this->invalidateElsewhere('tag1');
            return self::REGENERATED_DURING;
        });

        $this->assertSame(self::REGENERATED_DURING, $result);
        $this->assertSame(self::REGENERATED_AFTER, $this->item($worker, $tags)->get(fn () => self::REGENERATED_AFTER));
    }

    private function item(bool $worker, array $tags)
    {
        return $this->builder($worker)->addTags($tags)->make(self::KEY);
    }

    private function builder(bool $worker): CacheBuilder
    {
        $persistent = new StageableCache($this->backend);
        $local = fn () => $worker ? new NullCache() : new ArrayCache();
        $tagFactory = new TagFactory(new MemoizedCache($persistent, $local()));
        return new CacheBuilder($persistent, $local(), new TagSetFactory($tagFactory));
    }

    private function invalidateElsewhere(string $tag): void
    {
        $this->backend->put('tag_' . $tag, uniqid('elsewhere', true));
    }
}
