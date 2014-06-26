<?php
namespace GeekCache\Cache;

interface SoftInvalidatable extends Cache
{
    public function getStale( $key );
}
