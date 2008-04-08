<?php include HABARI_PATH . '/system/admin/header.php'; ?>

<div class="container">

	<?php if(Session::has_messages()) {Session::messages_out();} ?>
	
		<h2><?php _e( 'Manage Blogroll', 'blogroll' ); ?></h2>
		<p><?php _e( 'Below you can view and edit blogroll links. You can also add "quick links".', 'blogroll' ); ?></p>
	
		<form name="form_blogroll" id="form_blogroll" action="<?php URL::out('admin', 'page=blogroll'); ?>" method="post">
		
		<div class="container pagesplitter">
			<ul class="tabcontrol tabs">
				<li class="first last"><a href="#quick-link-blogroll"><?php _e( 'Add Quick Link', 'blogroll' ); ?></a></li>
			</ul>
			<div id="quick-link-blogroll" class="splitter">
				<div class="splitterinside">
					<div class="container">
						<p class="prepend-3"><?php _e( 'Enter the URL or feed URL, other information will be "auto-discovered".', 'blogroll' ); ?></p>
						
						<div class="container publish">
							<p class="prepend-2"><label for="quick_link" class="incontent"><?php _e( 'URL', 'blogroll' ); ?></label>
							<input type="text" id="quick_link" name="quick_link" size="100%" value="" class="styledformelement"></p>
						</div>
						
						<p id="formbuttons" class="prepend-2"><input type="submit" id="add_link" name="add_link" value="Add"></p>
					</div>
				</div>
			</div>
			
		</div>
		
	
	
	<table id="post-data-published" width="100%" cellspacing="0">
		<thead>
			<tr>
				<th align="right"> </th>
				<th align="left"><?php _e( 'Name', 'blogroll' ); ?></th>
				<th align="left"> </th>
				<th align="left"><?php _e( 'Owner', 'blogroll' ); ?></th>
				<th align="left"><?php _e( 'Description', 'blogroll' ); ?></th>
				<th align="center"> </th>
			</tr>
		</thead>
	
	<?php foreach ( Blogs::get() as $blog ) : ?>
	<tr>
			<td class="span-1 first"><input type="checkbox" name="blog_ids[]" value="<?php echo $blog->id; ?>"></td>
			<td class="span-4"><?php echo '<a href="' . $blog->url . '">' . Utils::truncate( $blog->name, 32, false ) . '</a>'; ?></td>
			<td class="span-1"><?php if ( $blog->feed ) : ?><a href="<?php echo $blog->feed; ?>"><img style="padding:0; margin:0 1em;" src="<?php echo $feed_icon; ?>"></a><?php endif; ?></td>
			<td class="span-4"><?php echo $blog->owner ?></td>
			<td class="span-10"><?php echo Utils::truncate( $blog->description, 128, false ); ?></td>
			<td class="span-2 last">
				<a class="edit" href="<?php URL::out('admin', 'page=blogroll&id=' . $blog->id); ?>" title="Edit this entry">
					Edit
				</a>
			</td>
		</tr>
		<?php endforeach; ?>
	
	<tr><td colspan="6" class="first last">
		<?php _e( 'Selected blogs: ', 'blogroll' ); ?>&nbsp;&nbsp;<select name="change" class="longselect">
		<option value="delete"><?php _e( 'Delete', 'blogroll' ); ?></option>
		<option value="auto_update"><?php _e( 'Auto Update', 'blogroll' ); ?></option>
		</select>
		<input type="submit" name="do_update" value="<?php _e( 'Update', 'blogroll' ); ?>">
	</td></tr>
	</table>
	</div>
	
	</form>
	
</div>

<?php include HABARI_PATH . '/system/admin/footer.php'; ?>
