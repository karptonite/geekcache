<?php
namespace Geek\Cache;

class TagSetFactory
{
	private $tagFactory;

	public function __construct( TagFactory $tagFactory )
	{
		$this->tagFactory = $tagFactory;
	}

	public function makeTagSet( array $keys )
	{
		$tags = array();

		foreach( $keys as $key )
			$tags[] = $this->tagFactory->makeTag( $key );

		return new TagSet( $tags );
	}
}
