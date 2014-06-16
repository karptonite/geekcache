<?php
namespace Geek\Cache;

class TagSetImpl implements TagSet
{
	private $tags = array();

	public function __construct( array $tags = array() )
	{
		foreach( $tags as $tag )
			if ( !( $tag instanceof Tag ) )
				throw new \InvalidArgumentException( "tags must be instances of Tag" );

		$this->tags = $tags;
	}
	
	public function getSignature()
	{
		$versions = array();

		foreach( $this->tags as $tag )
			$versions[] = $tag->getVersion();

		return sha1( implode( $versions ) );
	}
}
