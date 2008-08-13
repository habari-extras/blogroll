<?php
/*
 * Blogroll Plugin
 * Usage: <?php $theme->show_blogroll(); ?>
 * A sample blogroll.php template is included with the plugin.  This can be copied to your
 * active theme and modified to fit your preference.
 *
 * @todo Update wiki docs, and inline code docs
 */

class Blogroll extends Plugin
{
	const VERSION= '1.0-alpha';

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
	
	public function action_init() {
		Post::add_new_type('link');
		$this->add_template('blogroll_manage', dirname(__FILE__) . '/templates/blogroll_manage.php');
	}
	
	public function filter_adminhandler_post_loadplugins_main_menu( $menus )
	{
		$menus['blogroll_manage'] =  array( 'url' => URL::get( 'admin', 'page=blogroll_manage'), 'title' => _t('Manage Blogroll'), 'text' => _t('Manage Blogroll'), 'selected' => false, 'hotkey' => 'B' );
		return $menus;
	}
	
	public function action_admin_theme_get_blogroll_manage( $handler, $theme ) {
		$theme->admin_page= 'Manage Blogroll';
		$theme->admin_title= 'Manage Blogroll';
		
		$form= new FormUI('blogroll');
		$form->class[]= 'create';
		
		$tabs= $form->append('tabs', 'opml_tabs');
		
		$opml= $tabs->append('fieldset', 'opmltab', _t('Import/Export OPML'));
		
		$theme->form= $form;
		
	}
	
	public function action_publish_post($post, $form) {
		if($post->content_type != Post::type('link')) return;
		
		$this->action_form_publish($form, $post);
				
		if($form->quick_url->value != '') {
			$data= $this->get_info_from_url($form->quick_url->value);
			
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

		// exit();
	}
	
	public function action_form_publish($form, $post) {	
		
		if($post->content_type != Post::type('link')) return;
		
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
		
		$extras->append('select', 'relationship', 'null:null', _t('Relationship'), $this->get_relationships(), 'tabcontrol_select');
		$extras->relationship->value = $post->info->relationship;

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
	
	private function get_relationships() {
		return array(
			'external' => 'External',
			'nofollow' => 'Nofollow',
			'bookmark' => 'Bookmark'
		);
	}

}
?>