<?php

/**
 * 
 *
 * @package blogroll
 */

class Blog extends QueryRecord
{
	private $info= null;
	
	/**
	 * Returns the defined database columns for a cronjob.
	 * @return array Array of columns in the crontab table
	 */
	public static function default_fields()
	{
		return array(
			'id' => 0,
			'name' => '',
			'url' => '',
			'feed' => '',
			'description' => '',
			'owner' => '',
			'updated' => '',
		);
	}

	/**
	 * Constructor for the CronJob class.
	 * @param array $paramarray an associative array or querystring of initial field values
	 */
	public function __construct( $paramarray = array() )
	{
		// Defaults
		$this->fields= array_merge(
			self::default_fields(),
			$this->fields,
			$this->newfields
		);
		
		parent::__construct( $paramarray );
		$this->exclude_fields('id');
		
		$this->info= new BlogInfo ( $this->fields['id'] );
	}
	
	public static function get( $id )
	{
		return DB::get_row( "SELECT * FROM {blogroll} WHERE id = ?", array($id), 'Blog' );
	}
	
	public function __get( $name )
	{
		switch($name)
		{
			case 'info':
				$out = $this->get_info();
				break;
			default:
				$out = parent::__get( $name );
				break;
		}
		return $out;
	}
	
	private function get_info()
	{
		if ( ! $this->info ) {
			$this->info= new BlogInfo( $this->id );
		}
		return $this->info;
	}
	
	public function update_from_url()
	{
		if ( ! $this->feed && $this->url ) {
			$html= RemoteRequest::get_contents( $this->url );
			$this->feed= $this->get_feed_location( $html );
		}
		if ( $this->feed ) {
			$xml= new SimpleXMLElement( RemoteRequest::get_contents( $this->feed ) );
			switch ( $xml->getName() ) {
				case 'RDF':
				case 'rss':
					$this->name= (string) $xml->channel->title;
					$this->url= (string) $xml->channel->link;
					$this->description= (string) $xml->channel->description;
					break;
				case 'feed':
					$this->name= (string) $xml->title;
					$this->description= (string) $xml->subtitle;
					foreach ( $xml->link as $link ) {
						$atts= $link->attributes();
						if ( $atts['rel'] == 'alternate' ) {
							$this->url= (string) $atts['href'];
							break;
						}
					}
					break;
			}
			$this->update();
		}
		else {
			throw new Exception( _t( 'Could not find a feed for that blog', 'blogroll' ) );
		}
	}
	
	private function get_feed_location( $html )
	{
		preg_match_all( '/<link\s+(.*?)\s*\/?>/si', $html, $matches );
		$links= $matches[1];
		$final_links= array();
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
			if ( strtolower( $final_links[$n]['rel'] ) == 'alternate' ) {
				if ( strtolower( $final_links[$n]['type'] ) == 'application/rss+xml' ) {
					$href= $final_links[$n]['href'];
				}
				if ( !$href && strtolower( $final_links[$n]['type'] ) == 'text/xml' ) {
					$href= $final_links[$n]['href'];
				}
				if ( $href ) {
					if ( strstr( $href, "http://" ) !== false ) {
						$full_url= $href;
					}
					else {
						$url_parts= parse_url( $this->url );
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

	/**
	 * Saves a new cron job to the crontab table
	 */
	public function insert()
	{
		$result = parent::insertRecord( DB::table('blogroll') );
		$this->newfields['id'] = DB::last_insert_id(); // Make sure the id is set in the comment object to match the row id
		$this->fields = array_merge($this->fields, $this->newfields);
		$this->newfields = array();
		$this->info->commit( $this->fields['id'] );
		return $result;
	}

	/**
	 * Updates an existing cron job to the crontab table
	 */
	public function update()
	{
		$result = parent::updateRecord( DB::table('blogroll'), array('id'=>$this->id) );
		$this->fields = array_merge($this->fields, $this->newfields);
		$this->newfields = array();
		$this->info->commit();
		return $result;
	}

	/**
	 * Deletes an existing cron job
	 */
	public function delete()
	{
		return parent::deleteRecord( DB::table('blogroll'), array('id'=>$this->id) );
	}
}

?>
