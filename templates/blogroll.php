<!-- To customize this template, copy it to your currently active theme directory and edit it -->
<div id="blogroll">
	<h2><?php echo $blogroll_title; ?></h2>
	<ul>
	<?php if ( ! empty( $blogs ) ) { foreach( $blogs as $blog ) { ?>
		<li><a href="<?php echo $blog->info->url; ?>"><?php echo $blog->title; ?></a></li>
	<?php } } ?>
	</ul>
	
</div>