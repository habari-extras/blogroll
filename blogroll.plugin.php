<?php
/*
 * Blogroll Plugin
 * Usage: <?php $theme->show_blogroll(); ?>
 * A sample blogroll.php template is included with the plugin.  This can be copied to your
 * active theme and modified to fit your preference.
 *
 * @todo Update wiki docs, and inline code docs
 * url, feedurl, ownername, relationship
 */

class Blogroll2 extends Plugin
{
	const VERSION = '0.6-beta';
	const API_VERSION = 004;
	const CONTENT_TYPE = 'blogroll';
	
	private $relationships = array(
		'external' => 'External',
		'nofollow' => 'Nofollow',
		'bookmark' => 'Bookmark'
	);
	
	private $info_fields = array( 'feedurl', 'ownername', 'relationship', 'url' );

	public function info()
	{
		return array(
			'name' => 'Blogroll',
			'version' => self::VERSION,
			'url' => 'http://wiki.habariproject.org/en/plugins/blogroll',
			'author' => 'Habari Community',
			'authorurl' => 'http://habariproject.org/',
			'license' => 'Apache License 2.0',
			'description' => 'Displays a blogroll on your blog'
		);
	}
	
	/**
	 * Run activation routines, and setup default options.
	 */
	public function action_plugin_activation( $file )
	{
		if ( $file == str_replace( '\\','/', $this->get_file() ) ) {
			if ( ! CronTab::get_cronjob( 'blogroll:update' ) ) {
				CronTab::add_hourly_cron( 'blogroll:update', 'blogroll_update_cron', 'Updates the blog updated timestamp from weblogs.com' );
			}

			Options::set( 'blogroll__api_version', self::API_VERSION );
			Options::set( 'blogroll__use_updated', true );
			Options::set( 'blogroll__max_links', '10' );
			Options::set( 'blogroll__sort_by', 'id' );
			Options::set( 'blogroll__direction', 'ASC' );
			Options::set( 'blogroll__list_title', 'Blogroll' );

			Post::add_new_type(self::CONTENT_TYPE);
		}
	}
	
	/**
	 * Run deactivation routines.
	 */
	public function action_plugin_deactivation( $file )
	{
		if ( $file == str_replace( '\\','/', $this->get_file() ) ) {
			CronTab::delete_cronjob('blogroll:update');
			Options::delete('blogroll__api_version');
			// should we remove type/posts here?
		}
	}
	
	public function action_init() {
		// remove legacy tables and import
		if ( Options::get('blogroll__db_version') || Options::get('blogroll__api_version') < 004 ) {
			$this->upgrade_pre_004();
		}
		$this->add_template( 'blogroll', dirname($this->get_file()) . '/templates/blogroll.php' );
	}
	
	/**
	 * 
	 */
  	public function action_update_check()
  	{
    	Update::add( 'blogroll', '0420cf10-db83-11dc-95ff-0800200c9a66',  $this->info->version );
  	}
  	
  	/**
	 * 
	 */
	public function filter_plugin_config( $actions, $plugin_id )
	{

		if ( $this->plugin_id() == $plugin_id ){
			$actions[]= _t( 'Configure', 'blogroll' );
		}
		return $actions;
	}
	
	/**
	 * 
	 */
	public function action_plugin_ui( $plugin_id, $action )
	{
		if ( $this->plugin_id() == $plugin_id ) {
			switch ( $action ) {
				case _t( 'Configure', 'blogroll' ):
					$form = new FormUI( 'blogroll' );

					$title = $form->append( 'text', 'list_title', 'option:blogroll__list_title', _t( 'List title: ', 'blogroll' ) );

					$max = $form->append( 'text', 'max_links', 'option:blogroll__max_links', _t( 'Max. displayed links: ', 'blogroll') );

					$sort_bys = array_merge(
						array_combine( array_keys( Post::default_fields() ), array_map( 'ucwords', array_keys( Post::default_fields() ) ) ),
						array( 'random' => _t('Randomly', 'blogroll') )
						);
					$sortby = $form->append( 'select', 'sort_by', 'option:blogroll__sort_by', _t( 'Sort By: ', 'blogroll'), $sort_bys );

					$orders = array( 'ASC' => _t('Ascending' ,'blogroll'), 'DESC' => _t('Descending' ,'blogroll') );
					$order = $form->append( 'select', 'direction', 'option:blogroll__direction', _t( 'Order: ', 'blogroll'), $orders );

					$update = $form->append( 'checkbox', 'use_update', 'option:blogroll__use_update', _t( 'Use Weblogs.com to get updates? ', 'blogroll') );

					$form->append( 'submit', 'save', 'Save' );
					$form->out();
					break;
			}
		}
	}
	
	public function action_publish_post( Post $post, FormUI $form ) {
		if ( $post->content_type == Post::type(self::CONTENT_TYPE) ) {
			if($form->quick_url->value != '') {
				$data= $this->get_info_from_url($form->quick_url->value);
				
				if (!$data) {
					Session::error( _t("Could not find information for {$form->quick_url->value}", 'blogroll') );
					Utils::redirect( URL::get( 'admin', 'page=publish&content_type=link' ) );
					exit;
				}
				
				$post->title= $data['name'];
				$post->info->url= $data['url'];
				$post->content= $data['description'];
				$post->info->feedurl= $data['feed'];
				$post->slug= Utils::slugify($data['name']);
				
			} else {
				$post->info->url= $form->url->value;
				$post->info->feedurl= $form->feedurl->value;
				$post->info->ownername= $form->ownername->value;
				$post->info->relationship= $form->relationship->value;
			}
		}
	}
	
	public function action_form_publish( FormUI $form, Post $post) {	
		
		if( $post->content_type == Post::type(self::CONTENT_TYPE) ) {
		
			// Quick link button to automagically discover info
			$quicklink_controls= $form->append('tabs', 'quicklink_controls');
			
			$quicklink_tab= $quicklink_controls->append('fieldset', 'quicklink_tab', _t('Quick Link'));
			$quicklink_wrapper= $quicklink_tab->append('wrapper', 'quicklink_wrapper');
			$quicklink_wrapper->class='container';
			
			$quicklink_wrapper->append('text', 'quick_url', 'null:null', _t('Quick URL'), 'tabcontrol_text');
			$quicklink_wrapper->append('static', 'quick_url_info', '<p class="column span-15">Enter a url or feed url and other information will be automatically discovered.</p>');
			$quicklink_wrapper->append('submit', 'addquick', _t('Add'), 'admincontrol_submit');
			
			$quicklink_controls->move_before($quicklink_controls, $form);
			
			// Remove fields we don't need
			$form->silos->remove();
			$form->comments_enabled->remove();
			$form->newslug->remove();
			if($form->post_permalink != NULL) $form->post_permalink->remove();

			// Add the url field
			$form->append('text', 'url', 'null:null', _t('URL'), 'admincontrol_text');
			$form->url->class= 'important';
			$form->url->tabindex = 2;
			$form->url->value = $post->info->url;
			$form->url->move_after($form->title);
			
			// Retitle fields
			$form->title->caption= _t('Blog Name');
			$form->content->caption= _t('Description');
			$form->content->tabindex= 3;
			$form->tags->tabindex= 4;
			
			// Create the extras splitter & fields
			$extras = $form->settings;

			$extras->append('text', 'feedurl', 'null:null', _t('Feed URL'), 'tabcontrol_text');
			$extras->feedurl->value = $post->info->feedurl;
			
			$extras->append('text', 'ownername', 'null:null', _t('Owner Name'), 'tabcontrol_text');
			$extras->ownername->value = $post->info->ownername;
			
			$relationships = Plugins::filter( 'blogroll_relationships', $this->relationships );
			$extras->append('select', 'relationship', 'null:null', _t('Relationship'), $relationships, 'tabcontrol_select');
			$extras->relationship->value = $post->info->relationship;
		}
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
	
	public function filter_habminbar( $menu )
	{
		$menu['blogroll']= array( 'Blogroll', URL::get( 'admin', 'page=publish&content_type='.self::CONTENT_TYPE ) );
		return $menu;
	}

	public function filter_rewrite_rules( array $rules )
	{
		$rules[] = new RewriteRule(array(
			'name' => 'blogroll_opml',
			'parse_regex' => '/^blogroll\/opml\/?$/i',
			'build_str' => 'blogroll/opml',
			'handler' => 'ActionHandler',
			'action' => 'blogroll_opml',
			'priority' => 2,
			'rule_class' => RewriteRule::RULE_PLUGIN,
			'is_active' => 1,
			'description' => 'Rewrite for Blogroll OPML feed.'
		));
		return $rules;
	}
	
	public function action_handler_blogroll_opml( array $handler_vars )
	{
		$opml = new SimpleXMLElement( '<opml version="1.1"></opml>' );
		
		$head = $opml->addChild( 'head' );
		$head->addChild( 'title', Options::get( 'title' ) );
		$head->addChild( 'dateCreated', gmdate( 'D, d M Y G:i:s e' ) );
		
		$body = $opml->addChild( 'body' );
		
		$blogs = Posts::get(
			array(
				'content_type' => Post::type(self::CONTENT_TYPE),
				'nolimit' => TRUE,
				'status' => Post::status('published')
			)
		);
		
		foreach ( $blogs as $blog ) {
			$outline = $body->addChild( 'outline' );
			$outline->addAttribute( 'text', $blog->title );
			$outline->addAttribute( 'htmlUrl', $blog->info->url );
			$outline->addAttribute( 'xmlUrl', $blog->info->feedurl );
			$outline->addAttribute( 'ownername', $blog->info->ownername );
			$outline->addAttribute( 'relationship', $blog->info->relationship );
			$outline->addAttribute( 'pubdate', $blog->pubdate );
			$outline->addAttribute( 'updated', $blog->updated );
			$outline->addAttribute( 'type', 'link' );
		}
		$opml = Plugins::filter( 'blogroll_opml', $opml, $handler_vars );
		$opml = $opml->asXML();
		
		ob_clean();
		// header( 'Content-Type: application/opml+xml' );
		header( 'Content-Type: text/xml' );
		print $opml;
	}
	
	/**
	 * @todo fix this for use with Post object and content_type
	 */
	private function import_opml( SimpleXMLElement $xml )
	{
		$count = 0;
		foreach ( $xml->outline as $outline ) {
			$atts = (array) $outline->attributes();
			$params = $this->map_opml_atts( $atts['@attributes'] );
			if ( isset( $params['url'] ) && isset( $params['title'] ) ) {
				
				extract($params);
				$user = User::identify();
				$params = array(
					'title' => $title,
					'pubdate' => isset($pubdate) ? $pubdate : HabariDateTime::date_create(),
					'updated' => isset($updated) ? $updated : HabariDateTime::date_create(),
					'status' => Post::status('published'),
					'content_type' => Post::type(self::CONTENT_TYPE),
					'user_id' => $user->id,
				);
				$blog = Post::create($params);
				
				foreach($this->info_fields as $field ) {
					if ( isset(${$field}) && ${$field} ) {
						$blog->info->{$field} = ${$field};
					}
				}
				$count++;
			}
			if ( $outline->children() ) {
				$count += $this->import_opml( $outline );
			}
		}
		return $count;
	}
	
	/**
	 * Map standar OPML links to Post fields.
	 */
	private function map_opml_atts( $atts )
	{
		$atts = array_map( 'strval', $atts );
		$valid_atts = array_intersect_key(
			$atts,
			array_flip(
				array_merge(
					$this->info_fields,
					array( 'title', 'pubdate', 'updated')
				)
			)
		);
		foreach ( $atts as $key => $val ) {
			switch ( $key ) {
				case 'htmlUrl':
					$valid_atts['url']= $atts['htmlUrl'];
					break;
				case 'xmlUrl':
					$valid_atts['feedurl']= $atts['xmlUrl'];
					break;
				case 'text':
					$valid_atts['title']= $atts['text'];
					break;
			}
		}
		return $valid_atts;
	}
	
	/**
	 * @todo fix this for use with Post object and content_type
	 */
	public function filter_blogroll_update_cron( $success )
	{
		if ( Options::get( 'blogroll__use_updated' ) ) {
			$request = new RemoteRequest( 'http://www.weblogs.com/rssUpdates/changes.xml', 'GET' );
			$request->add_header( array( 'If-Modified-Since', Options::get('blogroll__last_update') ) );
			if ( $request->execute() ) {
				try {
					$xml = new SimpleXMLElement( $request->get_response_body() );
				}
				catch ( Exception $e ) {
					// log the failure here!
					return false;
				}
				$atts = $xml->attributes();
				$updated = strtotime( (string) $atts['updated'] );
				foreach ( $xml->weblog as $weblog ) {
					$atts = $weblog->attributes();
					$match = array();
					$match['url']= (string) $atts['url'];
					$match['feedurl']= (string) $atts['rssUrl'];
					$update = $updated - (int) $atts['when'];
					$post = Post::get(
						array(
							'any:info' => array( 'url' => $match['url'], 'feedurl' => $match['feedurl'] )
						)
					);
					if ( $post instanceof Post ) {
						$post->updated = HabariDateTime::create($update);
						$post->update();
					}
				}
				Options::set( 'blogroll__last_update', gmdate( 'D, d M Y G:i:s e' ) );
			}
			return true;
		}
		else {
			return false;
		}
	}
	
	public function upgrade_pre_004()
	{
		DB::register_table('blogroll');
		DB::register_table('bloginfo');
		DB::register_table('tag2blog');
		
		if ( ! in_array( DB::table('blogroll'), DB::list_tables() ) ) {
			Options::set( 'blogroll__api_version', self::API_VERSION );
			return;
		}
		
		Post::add_new_type(self::CONTENT_TYPE);
		
		$opml = new SimpleXMLElement( '<opml version="1.1"></opml>' );
		$head = $opml->addChild( 'head' );
		$head->addChild( 'title', Options::get( 'title' ) );
		$head->addChild( 'dateCreated', gmdate( 'D, d M Y G:i:s e' ) );
		$body = $opml->addChild( 'body' );
		
		$blogs = DB::get_results("SELECT * FROM {blogroll}", array());
		foreach ( $blogs as $blog ) {
			$outline = $body->addChild( 'outline' );
			$outline->addAttribute( 'text', $blog->name );
			$outline->addAttribute( 'htmlUrl', $blog->url );
			$outline->addAttribute( 'xmlUrl', $blog->feed );
			$outline->addAttribute( 'relation', $blog->rel );
			$outline->addAttribute( 'updated', $blog->updated );
			$outline->addAttribute( 'content', $blog->description );
			$outline->addAttribute( 'type', 'link' );
		}
		$this->import_opml($opml->body);
		
		DB::query('DROP TABLE IF EXISTS {blogroll}');
		DB::query('DROP TABLE IF EXISTS {bloginfo}');
		DB::query('DROP TABLE IF EXISTS {tag2blog}');
		Options::delete('blogroll__db_version');
		Options::set( 'blogroll__api_version', self::API_VERSION );
	}
}
?>