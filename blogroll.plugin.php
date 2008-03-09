<?php
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
	
	public function theme_sidebar( $theme )
	{
		echo $this->generate_blogroll();
	}
	
	private function generate_blogroll()
	{
	//read plugin options
	$title= Options::get( strtolower( get_class( $this ) ) . ':title' );
	$links= Options::get( strtolower( get_class( $this ) ) . ':links' );
	$max= Options::get( strtolower( get_class( $this ) ) . ':max');
	$randomize= Options::get( strtolower( get_class ( $this ) ) . ':random' );
	
	//set default values if options not set
	if ( empty( $max ) ) $max= 6;
	if ( empty( $random ) ) $random= false ;
	$out= '<div id="' . strtolower( get_class( $this ) ) . '">' . "\n" ;
	if (!isset($title)) $title='Blogroll';
		if (!empty ($title))
		{
			$out.= "<h2>" . $title . "</h2>\n";
		}
	$out.="<ul>\n";
	if ( $randomize ) shuffle( $links );
	foreach( $links as $link )
	{	
		$rel='';
		$link=strip_tags($link);
		$props= explode( '|', $link );
		if ( sizeof( $props ) > 2 )
		{
			$rel= '" rel="' . $props[2];
		}
		$out.= '<li><a href="' . $props[1] . '" title="Link to ' . $props[0] . $rel . '">' . $props[0] . "</a></li>\n";

		if ( $max != 0 )
		{
			if ( $max <= 1 ) break;
			$max--;
		}
	}
	$out.= "</ul>\n</div>";
	return $out;
	}
}
?>