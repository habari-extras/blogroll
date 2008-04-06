<!-- To customize this template, copy it to your currently active theme directory and edit it -->
<div id="blogroll">
	<ul>
	<?php if ( $blogs ) { foreach( $blogs as $blog ) { ?>
		<li><a href="<?php echo $blog->url; ?>"><?php echo $blog->name; ?></a></li>
	<?php } } ?>
	</ul>
	
</div>