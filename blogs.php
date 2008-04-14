<?php

/**
 * 
 *
 * @package blogroll
 */

class Blogs extends ArrayObject
{
	public function __get( $name )
	{
		switch( $name ) {
			case 'oneblog':
				return ( count( $this ) == 1 );
			case 'count':
				return count( $this );
		}
		return false;
	}
	
	public function get( $paramarray= array() )
	{	
		//convert to array if passed as a querystring	
		$paramarray= Utils::get_params( $paramarray );
		
		$wheres= array();
		$params= array();
		$where='';
		
		foreach ($paramarray as $key=>$value){
			if ($key != 'order_by' && $key != 'limit'){
				if ( isset( $value ) && $value != 'any' ){
					if ( is_array( $value ) ){
						$wheres[]= $key.' IN(' . implode( ',',array_fill( 0, $count($value), '?' ) ) . ')';
						$params[]= array_merge($params,$value);
					}
					else {
						$wheres[]= $key.'='.$value;
						$params[]= $value;
					}
				}
			}
		}
		
		extract($paramarray= Utils::get_params( $paramarray ));
		
		if ( isset( $order_by ) ){
			$order_by='ORDER BY ' . str_replace( 'random', 'RAND()', $order_by );
		}
		else {
			$order_by='';
		}
		
		if ( isset( $limit ) ){
			$limit='LIMIT '.$limit;
		}
		else {
			$limit='';
		}
		
		if ( !empty( $wheres ) ){
			$where='WHERE '. implode(' AND ',$wheres);
		}
		$query= "SELECT * FROM {blogroll} " . $where . ' ' . $order_by . ' ' . $limit;
		$results= DB::get_results($query, $params, 'Blog');
		
		$c= __CLASS__;
		return new $c( $results );
	}
	
	public static function get_info_from_url( $url )
	{
		$info= array();
		$data= RemoteRequest::get_contents( $url );
		$feed= self::get_feed_location( $data, $url );
		
		if ( $feed ) {
			$info['feed']= $feed;
			$data= RemoteRequest::get_contents( $feed );
		}
		else {
			$info['feed']= $url;
		}
		// try and parse the xml
		try {
			$xml= new SimpleXMLElement( $data );
			switch ( $xml->getName() ) {
				case 'RDF':
				case 'rss':
					$info['name']= (string) $xml->channel->title;
					$info['url']= (string) $xml->channel->link;
					if ( (string) $xml->channel->description ) $info['description']= (string) $xml->channel->description;
					break;
				case 'feed':
					$info['name']= (string) $xml->title;
					if ( (string) $xml->subtitle ) $info['description']= (string) $xml->subtitle;
					foreach ( $xml->link as $link ) {
						$atts= $link->attributes();
						if ( $atts['rel'] == 'alternate' ) {
							$info['url']= (string) $atts['href'];
							break;
						}
					}
					break;
			}
		}
		catch ( Exception $e ) {
			return array();
		}
		return $info;
	}
	
	public static function get_feed_location( $html, $url )
	{
		preg_match_all( '/<link\s+(.*?)\s*\/?>/si', $html, $matches );
		$links= $matches[1];
		$final_links= array();
		$href= '';
		$link_count= count( $links );
		for( $n= 0; $n < $link_count; $n++ ) {
			$attributes= preg_split('/\s+/s', $links[$n]);
			foreach ( $attributes as $attribute ) {
				$att= preg_split( '/\s*=\s*/s', $attribute, 2 );
				if ( isset( $att[1] ) ) {
					$att[1]= preg_replace( '/([\'"]?)(.*)\1/', '$2', $att[1] );
					$final_link[strtolower( $att[0] )]= $att[1];
				}
			}
			$final_links[$n]= $final_link;
		}
		for ( $n= 0; $n < $link_count; $n++ ) {
			if ( isset($final_links[$n]['rel']) && strtolower( $final_links[$n]['rel'] ) == 'alternate' ) {
				if ( isset($final_links[$n]['type']) && in_array( strtolower( $final_links[$n]['type'] ), array( 'application/rss+xml', 'application/atom+xml', 'text/xml' ) ) ) {
					$href= $final_links[$n]['href'];
				}
				if ( $href ) {
					if ( strstr( $href, "http://" ) !== false ) {
						$full_url= $href;
					}
					else {
						$url_parts= parse_url( $url );
						$full_url= "http://$url_parts[host]";
						if ( isset( $url_parts['port'] ) ) {
							$full_url.= ":$url_parts[port]";
						}
						if ( $href{0} != '/' ) {
							$full_url.= dirname( $url_parts['path'] );
							if ( substr( $full_url, -1 ) != '/' ) {
								$full_url.= '/';
							}
						}
						$full_url.= $href;
					}
					return $full_url;
				}
			}
		}
		return false;
	}
	
}

?>
