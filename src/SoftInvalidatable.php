<?php
namespace Geek\Cache;

interface SoftInvalidatable extends Cache
{
	public function getStale( $key );
}
