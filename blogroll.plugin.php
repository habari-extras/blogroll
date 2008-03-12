<?php
/*
 * Blogroll Plugin
 * Usage: <?php $theme->show_blogroll(); ?> 
 * A sample blogroll.php template is included with the plugin.  This can be copied to your 
 * active theme and modified to fit your preference.
 */

Class BlogRoll extends Plugin
{		
	public function info()
	{
		return array(
		'name'=>'BlogRoll',
		'version'=>'0.3',
		'url'=>'http://wiki.habariproject.org/en/plugins/blogroll',
		'author'=>'Habari Community',
		'authorurl'=>'http://habariproject.org/',
		'license'=>'Apache License 2.0',
		'description'=>'Displays a blogroll on your blog'
		);
	}
	
  	public function action_update_check()
  	{
    	Update::add( 'BlogRoll', '0420cf10-db83-11dc-95ff-0800200c9a66',  $this->info->version );
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
			$form= new FormUI( strtolower(get_class( $this ) ) );
			$title= $form->add('text','title','List title: ','Blogroll');
			$links= $form->add('textmulti','links','Links list: (use <i>name</i>|<i>url</i>|<i>rel(optional)</i> format)');
			$max= $form->add('text','max','Max. displayed links: ','6');
			$random=$form->add('checkbox','random','Randomize links ',false);
			$form->on_success( array( $this, 'saved_config' ) );
			$form->out();
			}
	}
	
	public function saved_config( $form )
	{   
		return true;
	}
	
	public function theme_show_blogroll( $theme )
	{
		//read plugin options
		$theme->blogroll_title= Options::get( strtolower( get_class( $this ) ) . ':title' );
		$links= Options::get( strtolower( get_class( $this ) ) . ':links' );
		$max= Options::get( strtolower( get_class( $this ) ) . ':max');
		$randomize= Options::get( strtolower( get_class ( $this ) ) . ':random' );
		
		//set default values if options not set
		if ( empty( $max ) ) $max= 6;
		if ( empty( $random ) ) $random= false ;
		if (!isset($theme->blogroll_title)) $theme->blogroll_title='Blogroll';
		
		if ( $randomize ) shuffle( $links );
		
		$blogroll_links= array();
		
		foreach( $links as $link )
		{	
			$link= strip_tags($link);
			$props= explode( '|', $link );
			if ( !isset( $props[2] ) ) {
				$props[2]= '';
			}
			
			$blogroll_links[]= array( 'title'=>"{$props[0]}", 'url'=>"{$props[1]}", 'rel'=>"{$props[2]}" );
			if ( $max != 0 ) {
				if ( $max <= 1 ) break;
				$max--;
			}
		}
		$theme->blogroll_links= $blogroll_links;
		return $theme->fetch( 'blogroll' );
		
	}
	
	public function action_init()
	{
		$this->add_template('blogroll', dirname(__FILE__) . '/blogroll.php');
	}
}
?>