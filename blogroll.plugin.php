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
			$links= $form->add('textmulti','links','Links list: (use <i>name</i>|<i>url</i> format)');
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
		return $this->generate_blogroll();
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
	if ( empty( $random ) ) $random= false;
	
	$out= "<div class=\"module\">\n";
	if ( $title )
	{
		$out.= '<h3>' . $title . "</h3>\n";
	}
	$out.="<ul>\n";
	if ( $randomize ) shuffle( $links );
	foreach( $links as $link )
	{
		$pair= explode( '|', $link );
		$out.= '<li><a href="' . $pair[1] . '" title="Link to ' . $pair[0] . '">' . $pair[0] . "</a></li>\n";
			if ( $max <= 1 ) break;
			$max--;
	}
	$out.= "</ul>\n</div>";
	return $out;
	}
}
?>