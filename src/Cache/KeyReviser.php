<?php
namespace GeekCache\Cache;

class KeyReviser
{
    public static function reviseKey(string $namespace, string $key): string
    {
        return $namespace . '_' . $key;
    }

}
