<?php

class BlogrollOPMLHandler extends ActionHandler
{
	public function act_blogroll_opml()
	{
		$opml= new SimpleXMLElement( '<opml version="1.1"></opml>' );
		
		$head= $opml->addChild( 'head' );
		$head->addChild( 'title', Options::get( 'title' ) );
		$head->addChild( 'dateCreated', gmdate( 'D, d M Y G:i:s e' ) );
		
		$body= $opml->addChild( 'body' );
		
		$blogs= Blogs::get();
		foreach ( $blogs as $blog ) {
			$outline= $body->addChild( 'outline' );
			$outline->addAttribute( 'text', $blog->name );
			$outline->addAttribute( 'htmlUrl', $blog->url );
			$outline->addAttribute( 'xmlUrl', $blog->feed );
			$outline->addAttribute( 'type', 'link' );
			$feilds= array_diff_key( $blog->to_array(), array_flip( array('id', 'name', 'url', 'feed') ) );
			foreach ( $feilds as $key => $value ) {
				if ( $value ) {
					$outline->addAttribute( $key, $value );
				}
			}
		}
		$opml= Plugins::filter( 'blogroll_opml', $opml, $this->handler_vars );
		$opml= $opml->asXML();
		
		ob_clean();
		// header( 'Content-Type: application/opml+xml' );
		header( 'Content-Type: text/xml' );
		print $opml;
	}
}

?>