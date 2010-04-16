<h3><?php echo $comment->title; ?></h3>
<ul>
	<?php $links = $content->list;
	Utils::debug( $links );
	foreach( $links as $label => $href ): ?>
	<li><a href="<?php echo $label; ?>"><?php echo $href; ?></a></li>
	<?php endforeach; ?>
</ul>