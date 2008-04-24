<!-- To customize this template, copy it to your currently active theme directory and edit it -->
<div id="blogroll">
	<h2><?php echo Options::get( 'blogroll:list_title' ); ?></h2>
	<ul>
	<?php if ( ! empty( $blogs ) ) { foreach( $blogs as $blog ) { ?>
		<li><a href="<?php echo $blog->url; ?>"><?php echo $blog->name; ?></a></li>
	<?php } } ?>
	</ul>
	
</div>