<?php
namespace Geek\Cache;

interface IncrementableCache extends Cache
{
	public function increment( $key, $value = 1 );
}
