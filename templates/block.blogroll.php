<h3><?php echo $content->title; ?></h3>
<ul>
	<?php $links = $content->list;

	foreach( $links as $link ): ?>
		<li class="vcard"><a href="<?php echo $link[ 'url' ]; ?>" class="url" title="<?php echo $link[ 'content' ]; ?>" rel="<?php echo $link[ 'relationship' ] ?>"><?php echo $link[ 'title' ]; ?></a></li>
	<?php endforeach; ?>
</ul>