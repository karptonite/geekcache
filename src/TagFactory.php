<?php
namespace Geek\Cache;

class TagFactory
{
	private $cache; 

	public function __construct( Cache $cache )
	{
		$this->cache = $cache;
	}
	
	public function makeTag( $key )
	{
		return new Tag( $this->cache, $key );
	}
}
