<?php
/*
 * Blogroll Plugin
 * Usage: <?php $theme->show_blogroll(); ?> 
 * A sample blogroll.php template is included with the plugin.  This can be copied to your 
 * active theme and modified to fit your preference.
 *
 * @todo add plugin filters for insert/update etc...
 * @todo use page splitter and plugin filter for additional feilds
 */

require_once "blogs.php";
require_once "bloginfo.php";
require_once "blog.php";

class Blogroll extends Plugin
{		
	public function info()
	{
		return array(
		'name' => 'Blogroll',
		'version' => '0.3',
		'url' => 'http://wiki.habariproject.org/en/plugins/blogroll',
		'author' => 'Habari Community',
		'authorurl' => 'http://habariproject.org/',
		'license' => 'Apache License 2.0',
		'description' => 'Displays a blogroll on your blog'
		);
	}
	
	public function action_plugin_activation( $file )
	{
		if ( $file == str_replace( '\\','/', $this->get_file() ) ) {
			DB::register_table( 'blogroll' );
			DB::register_table( 'bloginfo' );
			
			if ( ! CronTab::get_cronjob( 'blogroll:update' ) ) {
				$paramarray = array(
					'name' => 'blogroll:update',
					'callback' => 'blogroll_update_cron',
					'increment' => 1800, // one half hour
					'description' => 'Updates the blog updated timestamp from weblogs.com'
				);
				CronTab::add_cron( $paramarray );
			}
			
			Options::set( 'blogroll:use_updated', true );
			Options::set( 'blogroll:max_links', '10' );
			Options::set( 'blogroll:sort_by', 'updated' );
			Options::set( 'blogroll:list_title', 'Blogroll' );
			
			if ( $this->install_db_tables() ) {
				Session::notice( _t( 'Created the Blogroll database tables.', 'blogroll' ) );
			}
			else {
				Session::error( _t( 'Could not install Blogroll database tables.', 'blogroll' ) );
			}
		}
	}
	
	public function install_db_tables()
	{
		switch ( DB::get_driver_name() ) {
			case 'mysql':
				$schema= "CREATE TABLE " . DB::table('blogroll') . " (
				id INT UNSIGNED NOT NULL AUTO_INCREMENT,
				name VARCHAR(255) NOT NULL,
				url VARCHAR(255) NOT NULL,
				feed VARCHAR(255) NOT NULL,
				owner VARCHAR(255) NOT NULL,
				updated VARCHAR(12) NOT NULL,
				description TEXT,
				UNIQUE KEY id (id)
				);
				CREATE TABLE " . DB::table('bloginfo') . " (
				blog_id INT UNSIGNED NOT NULL,
				name VARCHAR(255) NOT NULL,
				type SMALLINT UNSIGNED NOT NULL DEFAULT 0,
				value TEXT,
				PRIMARY KEY (blog_id,name)
				);";
				break;
			case 'sqlite':
				$schema= "";
				break;
		}
		return DB::dbdelta( $schema );
	}
	
  	public function action_update_check()
  	{
    	Update::add( 'blogroll', '0420cf10-db83-11dc-95ff-0800200c9a66',  $this->info->version );
  	}
	
	public function action_init()
	{
		DB::register_table( 'blogroll' );
		DB::register_table( 'bloginfo' );
	}
	
	public function filter_plugin_config( $actions, $plugin_id )
	{
		
		if ( $this->plugin_id() == $plugin_id ){
			$actions[]= _t( 'Configure', 'blogroll' );
		}
		return $actions;
	}
	
	public function action_plugin_ui( $plugin_id, $action )
	{
		if ( $this->plugin_id()==$plugin_id ) {
			switch ( $action ) {
				case _t( 'Configure', 'blogroll' ):
					$form= new FormUI( 'blogroll' );
					$title= $form->add( 'text', 'list_title', _t( 'List title: ', 'blogroll' ) );
					$max= $form->add( 'text', 'max_links', _t( 'Max. displayed links: ', 'blogroll') );
					$random= $form->add( 'text', 'sort_by', _t( 'Randomize links ', 'blogroll') );
					$update= $form->add( 'checkbox', 'use_update', _t( 'Use Weblogs.com to get updates? ', 'blogroll') );
					$form->out();
					break;
			}
		}
	}
	
	public function filter_adminhandler_post_loadplugins_main_menu( $menu )
	{
		$menu['manage']['submenu']['blogroll']= array( 'caption' => _t( 'Blogroll', 'blogroll' ), 'url' => URL::get( 'admin', 'page=blogroll' ) );
		$menu['publish']['submenu']['blogroll']= array( 'caption' => _t( 'Blogroll', 'blogroll' ), 'url' => URL::get( 'admin', 'page=blogroll&add' ) );
		return $menu;
	}
	
	public function action_admin_theme_post_blogroll( $handler, $theme )
	{
		$params= array_intersect_key( $handler->handler_vars, array_flip( array('name', 'url', 'feed', 'description', 'owner') ) );
		
		if ( isset( $handler->handler_vars['change'] ) && isset( $handler->handler_vars['blog_ids'] ) ) {
			$action= $handler->handler_vars['change'];
			$count= count($handler->handler_vars['blog_ids']);
			$blog_ids= (array) $handler->handler_vars['blog_ids'];
			
			switch ( $action ) {
				case 'delete':
					foreach ( $blog_ids as $blog_id ) {
						$blog= Blog::get( $blog_id );
						$blog->delete();
					}
					Session::notice( sprintf( _n('Deleted %d blog', 'Deleted %d blogs', $count, 'blogroll'), $count ) );
					break;
				case 'auto_update':
					foreach ( $blog_ids as $blog_id ) {
						$blog= Blog::get( $blog_id );
						if ( $info= Blog::get_info_from_url( $blog->feed?$blog->feed:$blog->url ) ) {
							foreach ( $info as $key => $value ) {
								$blog->$key= $value;
							}
							$blog->update();
						}
						else {
							Session::error( sprintf( _t('Could not fetch info for %s', 'blogroll'), $blog->name ) );
							$count--;
						}
					}
					Session::notice( sprintf( _n('Automatically updated %d blog', 'Automatically updated %d blogs', $count, 'blogroll'), $count ) );
					break;
			}
			
			Utils::redirect( URL::get( 'admin', 'page=blogroll' ) );
			exit;
		}
		
		if ( isset( $handler->handler_vars['quick_link'] ) && $handler->handler_vars['quick_link'] ) {
			$link= $handler->handler_vars['quick_link'];
			if ( $link && strpos( $link, 'http://' ) !== 0 ) {
				$link= 'http://' . $link;
			}
			if ( $info= Blog::get_info_from_url( $link ) ) {
				$params= $info;
			}
			else {
				$_POST['url']= $link;
				$_POST['feed']= $link;
				Session::add_to_set( 'last_form_data', $_POST, 'get' );
				Session::error( sprintf( _t('Could not fetch info from %s. Please enter the information manually.', 'blogroll'), $link ) );
				Utils::redirect( URL::get( 'admin', 'page=blogroll&add' ) );
				exit;
			}
		}
		
		if ( $params && ( empty( $params['name'] ) || empty( $params['url'] ) ) ) {
			Session::error( _t('Blog Name and URL are required feilds.', 'blogroll') );
			Session::add_to_set( 'last_form_data', $_POST, 'get' );
			Utils::redirect( URL::get( 'admin', 'page=blogroll&add' ) );
			exit;
		}
		
		if ( isset( $handler->handler_vars['id'] ) ) {
			$blog= Blog::get( $handler->handler_vars['id'] );
			foreach ( $params as $key => $value ) {
				$blog->$key= $value;
			}
			$blog->update();
			Session::notice( sprintf( _t('Updated blog %s'), $blog->name ) );
		}
		elseif ( $params ) {
			$blog= new Blog( $params );
			if ( $blog->insert() ) {
				Session::notice( sprintf( _t('Successfully added blog %s'), $blog->name ) );
				
			}
			else {
				Session::notice( sprintf( _t( 'Could not add blog %s'), $blog->name ) );
			}
		}
		Utils::redirect( URL::get( 'admin', 'page=blogroll' ) );
		exit;
	}
	
	public function action_admin_theme_get_blogroll( $handler, $theme )
	{
		Stack::add( 'admin_stylesheet', array( $this->get_url() . '/templates/blogroll.css', 'screen' ) );
		$theme->feed_icon= $this->get_url() . '/templates/feed.png';
		if ( isset( $handler->handler_vars['id'] ) ) {
			$blog= Blog::get( $handler->handler_vars['id'] );
			foreach ( $blog->to_array() as $key => $value ) {
				$theme->$key= $value;
			}
			$theme->display( 'blogroll_admin_edit' );
			return;
		}
		elseif ( isset( $handler->handler_vars['add'] ) ) {
			$theme->display( 'blogroll_admin_edit' );
			return;
		}
		$theme->display( 'blogroll_admin' );
	}
	
	public function filter_available_templates( $templates, $class ) {
		$templates= array_merge( $templates, array('blogroll_admin','blogroll_admin_edit','blogroll') );
		return $templates;
	}
	
	public function filter_include_template_file( $template_path, $template_name, $class )
	{
		if ( ! file_exists( $template_path ) ) {
			switch ( $template_name ) {
				case 'blogroll_admin':
					return dirname( __FILE__ ) . '/templates/blogroll_admin.php';
				case 'blogroll_admin_edit':
					return dirname( __FILE__ ) . '/templates/blogroll_admin_edit.php';
				case 'blogroll':
					return dirname( __FILE__ ) . '/templates/blogroll.php';
			}
		}
		return $template_path;
	}
	
	public function theme_show_blogroll( $theme )
	{
		$theme->blogroll_title= Options::get( 'blogroll:list_title' );
		$theme->blogs= Blogs::get();
		
		return $theme->fetch( 'blogroll' );
		
	}
	
	public function filter_blogroll_update_cron( $success )
	{
		if ( Options::get( 'blogroll:use_updated' ) ) {
			$request= new RemoteRequest( 'http://www.weblogs.com/rssUpdates/changes.xml', 'GET' );
			$request->add_header( array( 'If-Modified-Since', Options::get('blogroll:last_update') ) );
			if ( $request->execute() ) {
				$xml= new SimpleXMLElement( $request->get_response_body() );
				$atts= $xml->attributes();
				$updated= strtotime( (string) $atts['updated'] );
				foreach ( $xml->weblog as $weblog ) {
					$atts= $weblog->attributes();
					$match= array();
					$match['url']= (string) $atts['url'];
					$match['feed']= (string) $atts['rssUrl'];
					$update= $updated - (int) $atts['when'];
					if ( DB::exists( DB::table( 'blogroll' ), $match ) ) {
						DB::update( DB::table( 'blogroll' ), array( 'updated' => $update ), $match );
					}
				}
				Options::set( 'blogroll:last_update', gmdate( 'D, d M Y G:i:s e' ) );
			}
			return true;
		}
		else {
			return false;
		}
	}
}
?>