<!-- To customize this template, copy it to your currently active theme directory and edit it -->
<div id="blogroll">
	<h2><?php echo $blogroll_title; ?></h2>
	<ul>
	<?php if ( ! empty( $blogs ) ) { foreach( $blogs as $blog ) { ?>
		<li class="vcard"><a href="<?php echo $blog->info->url; ?>" class="url" title="<?php echo $blog->content; ?>" rel="<?php echo $blog->info->relationship; ?> <?php echo $blog->xfn_relationships; ?>"><?php echo $blog->title; ?></a></li>
	<?php } } ?>
	</ul>
</div>