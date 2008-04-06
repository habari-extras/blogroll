<?php

/**
 * 
 *
 * @package blogroll
 */

class Blogs extends ArrayObject
{
	public function get()
	{
		$results= DB::get_results( 'SELECT * FROM {blogroll}', array(), 'Blog' );
		$c= __CLASS__;
		return new $c( $results );
	}
	
}

?>
