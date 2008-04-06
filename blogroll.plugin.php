<?php
/*
 * Blogroll Plugin
 * Usage: <?php $theme->show_blogroll(); ?> 
 * A sample blogroll.php template is included with the plugin.  This can be copied to your 
 * active theme and modified to fit your preference.
 */

require_once "blogs.php";
require_once "bloginfo.php";
require_once "blog.php";

class Blogroll extends Plugin
{		
	public function info()
	{
		return array(
		'name'=>'Blogroll',
		'version'=>'0.3',
		'url'=>'http://wiki.habariproject.org/en/plugins/blogroll',
		'author'=>'Habari Community',
		'authorurl'=>'http://habariproject.org/',
		'license'=>'Apache License 2.0',
		'description'=>'Displays a blogroll on your blog'
		);
	}
	
	public function action_plugin_activation( $file )
	{
		if ( $file == $this->get_file() ) {
			DB::register_table( 'blogroll' );
			DB::register_table( 'bloginfo' );
			Options::set( 'blogroll:use_updated', true );
			Options::set( 'blogroll:max_links', '10' );
			Options::set( 'blogroll:sort_by', 'updated' );
			Options::set( 'blogroll:list_title', 'Blogroll' );
			
			$table= DB::dbdelta(
				"CREATE TABLE " . DB::table('blogroll') . " (
				id INT UNSIGNED NOT NULL AUTO_INCREMENT,
				name VARCHAR(255) NOT NULL,
				url VARCHAR(255) NOT NULL,
				feed VARCHAR(255) NOT NULL,
				owner VARCHAR(255) NOT NULL,
				updated VARCHAR(12) NOT NULL,
				description TEXT,
				UNIQUE KEY id (id)
				);"
			);
			$info= DB::dbdelta(
				"CREATE TABLE " . DB::table('bloginfo') . " (
				blog_id INT UNSIGNED NOT NULL,
				name VARCHAR(255) NOT NULL,
				type SMALLINT UNSIGNED NOT NULL DEFAULT 0,
				value TEXT,
				PRIMARY KEY (blog_id,name)
				);"
			);
			if ( $table && $info ) {
				Session::notice( _t( 'Created the Blogroll database tables.', 'blogroll' ) );
			}
			else {
				Session::error( _t( 'Could not install Blogroll database tables.', 'blogroll' ) );
			}
		}
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
			$actions[]= 'Configure';		
		}
		return $actions;
	}
	
	public function action_plugin_ui( $plugin_id, $action )
	{
		if ( $this->plugin_id()==$plugin_id && $action=='Configure' ){
			$form= new FormUI( 'blogroll' );
			$title= $form->add( 'text', 'list_title', 'List title: ' );
			$max= $form->add( 'text', 'max_links', 'Max. displayed links: ' );
			$random= $form->add( 'text', 'sort_by', 'Randomize links ' );
			$update= $form->add( 'checkbox', 'use_update', 'Use Pingomatic to get updates? ' );
			$form->out();
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
		if ( isset( $handler->handler_vars['change'] ) && $handler->handler_vars['change'] == 'delete' && isset( $handler->handler_vars['blog_ids'] ) ) {
			foreach ( (array) $handler->handler_vars['blog_ids'] as $blog_id ) {
				$blog= Blog::get( $blog_id );
				$blog->delete();
			}
			$count= count($handler->handler_vars['blog_ids']);
			Session::notice( _n( sprintf('Deleted %d blog',$count), sprintf('Deleted %d blogs',$count), $count ) );
		}
		elseif ( isset( $handler->handler_vars['id'] ) ) {
			$blog= Blog::get( $handler->handler_vars['id'] );
			$blog->name= $params['name'];
			$blog->url= $params['url'];
			$blog->feed= $params['feed'];
			$blog->owner= $params['owner'];
			$blog->description= $params['description'];
			$blog->update();
			Session::notice( _t( 'Updated blog ' . $blog->name ) );
		}
		else {
			$blog= new Blog( $params );
			if ( $blog->insert() ) {
				Session::notice( _t( 'Successfully added blog ' . $blog->name ) );
				if ( isset( $handler->handler_vars['auto_update'] ) ) {
					try {
						$blog->update_from_url();
						Session::notice( sprintf( _t('Automatically updated info for %s (%s)'), $blog->name, $blog->url ) );
					}
					catch ( Exception $e ) {
						Session::error( _t( 'Could not fetch info from ' . $blog->url . '. Please add the information below.' ) );
						Session::error( $e->getMessage() );
						Utils::redirect( URL::get( 'admin', 'page=blogroll&id=' . $blog->id ) );
						return;
					}
				}
			}
			else {
				Session::notice( _t( 'Could not add blog ' . $blog->name ) );
			}
		}
		Utils::redirect( URL::get( 'admin', 'page=blogroll' ) );
	}
	
	public function action_admin_theme_get_blogroll( $handler, $theme )
	{
		Stack::add( 'admin_stylesheet', array( $this->get_url() . '/templates/blogroll.css', 'screen' ) );
		if ( isset( $handler->handler_vars['id'] ) ) {
			$blog= Blog::get( $handler->handler_vars['id'] );
			if ( isset( $handler->handler_vars['auto_update'] ) ) {
				try {
					$blog->update_from_url();
					Session::notice( sprintf( _t('Automatically updated info for %s (%s)'), $blog->name, $blog->url ) );
				}
				catch ( Exception $e ) {
					Session::error( _t( 'Could not fetch info from ' . $blog->url ) );
				}
			}
			else {
				$theme->id= $blog->id;
				$theme->name= $blog->name;
				$theme->url= $blog->url;
				$theme->feed= $blog->feed;
				$theme->owner= $blog->owner;
				$theme->description= $blog->description;
				$theme->display( 'blogroll_admin_edit' );
				return;
			}
		}
		elseif ( isset( $handler->handler_vars['add'] ) ) {
			$theme->id= '';
			$theme->name= '';
			$theme->url= '';
			$theme->feed= '';
			$theme->owner= '';
			$theme->description= '';
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
		$theme->blogroll_title= Options::get( 'blogroll:title' );
		$theme->blogs= Blogs::get();
		
		return $theme->fetch( 'blogroll' );
		
	}
}
?>