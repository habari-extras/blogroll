<?php

class BlogInfo extends InfoRecords {

	function __construct ( $blog_id ) {
		parent::__construct ( DB::table('blogrollinfo'), 'blog_id', $blog_id ); // call parent with appropriate  parameters
	}

}

?>