<?php
namespace GeekCache\Cache;

interface Counter extends Cache
{
	public function increment( $key, $value = 1 );
}
