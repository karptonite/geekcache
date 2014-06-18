<?php
namespace Geek\Cache;

interface FreshnessPolicy
{
	public function packValueWithPolicy( $value, $ttl );
	public function unpackValue( $result );
	public function resultIsFresh( $result );
	public function computeTtl( $ttl );
}
