<?php
namespace GeekCache\Cache;

class TagSetFactory
{
	private $tagFactory;

	public function __construct( TagFactory $tagFactory )
	{
		$this->tagFactory = $tagFactory;
	}

	public function makeTagSet( $names )
	{
		$tags = array();
		$names = is_array( $names ) ? $names : func_get_args();

		foreach( $names as $name )
			$tags[] = $this->tagFactory->makeTag( $name );

		return new TagSet( $tags );
	}
}
