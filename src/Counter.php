<?php
namespace Geek\Cache;

interface Counter extends Cache
{
	public function increment( $key, $value = 1 );
}
