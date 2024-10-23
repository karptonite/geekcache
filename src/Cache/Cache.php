<?php

namespace GeekCache\Cache;

interface Cache
{
    public function stage(string $key): void;
    public function unstage(string $key): void;
    public function get($key, callable $regenerator = null, $ttl = 0);
    public function put($key, $value, $ttl = 0);
    public function delete($key);
    public function clear();
    // the functions below are used only for debugging
    public function getGetCount(): int;
}
