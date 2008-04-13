<?php

/**
 *
 *
 * @package blogroll
 */

class Blog extends QueryRecord
{
	private $info= null;
	private $tags= null;
	
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
			'rel' => 'external',
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
		if ( isset( $this->fields['tags'] ) ) {
			$this->tags= $this->parsetags( $this->fields['tags'] );
			unset( $this->fields['tags'] );
		}
		$this->exclude_fields('id');
		
		$this->info= new BlogInfo ( $this->fields['id'] );
	}
	
	public static function get( $id )
	{
		return DB::get_row( "SELECT * FROM {blogroll} WHERE id = ?", array($id), 'Blog' );
	}
	
	private static function parsetags( $tags )
	{
		if ( is_string( $tags ) ) {
			if ( '' === $tags ) {
				return array();
			}
			// dirrty ;)
			$rez= array( '\\"'=>':__unlikely_quote__:', '\\\''=>':__unlikely_apos__:' );
			$zer= array( ':__unlikely_quote__:'=>'"', ':__unlikely_apos__:'=>"'" );
			// escape
			$tagstr= str_replace( array_keys( $rez ), $rez, $tags );
			// match-o-matic
			preg_match_all( '/((("|((?<= )|^)\')\\S([^\\3]*?)\\3((?=[\\W])|$))|[^,])+/', $tagstr, $matches );
			// cleanup
			$tags= array_map( 'trim', $matches[0] );
			$tags= preg_replace( array_fill( 0, count( $tags ), '/^(["\'])(((?!").)+)(\\1)$/'), '$2', $tags );
			// unescape
			$tags= str_replace( array_keys( $zer ), $zer, $tags );
			// hooray
			return $tags;
		}
		elseif ( is_array( $tags ) ) {
			return $tags;
		}
	}

	private function save_tags()
	{
		DB::query( 'DELETE FROM ' . DB::table( 'tag2blog' ) . ' WHERE blog_id = ?', array( $this->fields['id'] ) );
		if ( count( $this->tags ) == 0 ) {
			return;
		}
		foreach ( ( array ) $this->tags as $tag ) {
			$tag_slug= Utils::slugify( $tag );
			// @todo TODO Make this multi-SQL safe!
			if ( DB::get_value( 'SELECT count(*) FROM ' . DB::table( 'tags' ) . ' WHERE tag_text = ?', array( $tag ) ) == 0 ) {
				DB::query( 'INSERT INTO ' . DB::table( 'tags' ) . ' (tag_text, tag_slug) VALUES (?, ?)', array( $tag, $tag_slug ) );
			}
			DB::query( 'INSERT INTO ' . DB::table( 'tag2blog' ) . ' (tag_id, blog_id) SELECT id AS tag_id, ? AS blog_id FROM ' . DB::table( 'tags' ) . ' WHERE tag_text = ?',
				array( $this->fields['id'], $tag )
			);
		}
	}
	
	public function __set( $name, $value )
	{
		switch( $name ) {
			case 'tags':
				$this->tags= $this->parsetags( $value );
				return $this->get_tags();
		}
		return parent::__set( $name, $value );
	}
	
	
	public function __get( $name )
	{
		$fieldnames= array_merge( array_keys( $this->fields ), array( 'tags' ) );
		if ( !in_array( $name, $fieldnames ) && strpos( $name, '_' ) !== false ) {
			preg_match( '/^(.*)_([^_]+)$/', $name, $matches );
			list( $junk, $name, $filter )= $matches;
		}
		else {
			$filter= false;
		}
		
		switch($name)
		{
			case 'info':
				$out = $this->get_info();
				break;
			case 'tags':
				$out= $this->get_tags();
				break;
			default:
				$out = parent::__get( $name );
				break;
		}
		$out= Plugins::filter( "blog_{$name}", $out, $this );
		if ( $filter ) {
			$out= Plugins::filter( "blog_{$name}_{$filter}", $out, $this );
		}
		return $out;
	}
	
	private function get_tags()
	{
		if ( empty( $this->tags ) ) {
			$sql= "
				SELECT t.tag_text, t.tag_slug
				FROM " . DB::table( 'tags' ) . " t
				INNER JOIN " . DB::table( 'tag2blog' ) . " t2b
				ON t.id = t2b.tag_id
				WHERE t2b.blog_id = ?
				ORDER BY t.tag_slug ASC";
			$result= DB::get_results( $sql, array( $this->fields['id'] ) );
			if ( $result ) {
				foreach ( $result as $t ) {
					$this->tags[$t->tag_slug]= $t->tag_text;
				}
			}
		}
		if ( count( $this->tags ) == 0 ) {
			return array();
		}
		return $this->tags;
	}
	
	private function get_info()
	{
		if ( ! $this->info ) {
			$this->info= new BlogInfo( $this->id );
		}
		return $this->info;
	}

	/**
	 * Saves a new cron job to the crontab table
	 */
	public function insert()
	{
		Plugins::act( 'blogroll_insert_before', $this );
		
		$result = parent::insertRecord( DB::table('blogroll') );
		$this->newfields['id'] = DB::last_insert_id(); // Make sure the id is set in the comment object to match the row id
		$this->fields = array_merge($this->fields, $this->newfields);
		$this->newfields = array();
		$this->info->commit( $this->fields['id'] );
		$this->save_tags();
		
		Plugins::act( 'blogroll_insert_after', $this );
		return $result;
	}

	/**
	 * Updates an existing cron job to the crontab table
	 */
	public function update()
	{
		Plugins::act( 'blogroll_update_before', $this );
		
		$result = parent::updateRecord( DB::table('blogroll'), array('id'=>$this->id) );
		$this->fields = array_merge($this->fields, $this->newfields);
		$this->newfields = array();
		$this->save_tags();
		$this->info->commit();
		
		Plugins::act( 'blogroll_update_after', $this );
		return $result;
	}

	/**
	 * Deletes an existing cron job
	 */
	public function delete()
	{
		Plugins::act( 'blogroll_delete_before', $this );
		
		if ( isset( $this->info ) ) {
			$this->info->delete_all();
		}
		$result= parent::deleteRecord( DB::table('blogroll'), array('id'=>$this->id) );
		
		Plugins::act( 'blogroll_delete_after', $this );
		return $result;
	}
}

?>
