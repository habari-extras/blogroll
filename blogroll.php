<!-- To customize this template, copy it to your currently active theme directory and edit it -->
<div id="blogroll">
	<h2><?php echo $blogroll_title; ?></h2>
	<ul>
	<?php foreach ( $blogroll_links as $item ) : ?>
		<li><a href="<?php echo $item['url']; ?>" title="<?php echo $item['title']; ?>" rel="<?php echo $item['rel']; ?>"><?php echo $item['title']; ?></a></li>
	<?php endforeach; ?>
	</ul>
</div>