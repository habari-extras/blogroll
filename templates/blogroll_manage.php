<?php $theme->display('header'); ?>

	<div class="container">
		<h2><?php _e( 'Manage Blogroll', 'blogroll' ); ?></h2>
		<p>
		<?php _e( 'Below you can view and edit blogroll links. You can also import links from an OPML file.', 'blogroll' ); ?>
		<?php printf( _t( 'Or you can <a href="%s">publish a new Blogroll link</a>.', 'blogroll' ), URL::get( 'admin', 'page=publish&content_type=link' ) ); ?>
		</p>
	</div>

	<?php $form->out(); ?>

<?php $this->display('footer'); ?>