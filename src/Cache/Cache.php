<?php

namespace GeekCache\Cache;

interface Cache
{
    // if $skipIfStaged is set, and the key it corresponds to is already staged
    // do not stage this new key.
    public function stage(string $key, ?string $skipIfStaged = null): void;
    public function get($key, ?callable $regenerator = null, $ttl = 0);
    public function put($key, $value, $ttl = 0);
    public function delete($key);
    public function clear();
    // below are internal functions, not intended for use outside the library
    
    // decrementStagedCount will mark a staged result as if were read, if we had previously indicated
    // it would be read, and now know the result before reading from staging,
    // or if the result is now irrelevant, and can be released.
    public function decrementStagedCount(string $key): void;
    // the functions below are used only for debugging
    public function getGetCount(): int;
}
